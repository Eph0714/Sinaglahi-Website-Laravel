<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistSocialLink;
use App\Models\Artwork;
use App\Models\AspNetRole;
use App\Models\AspNetUser;
use App\Services\ArtistLookupData;
use App\Services\FileStorageService;
use App\Services\SlugHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Artist Management module - mirrors Areas/Admin/Controllers/ArtistsController
 * in the .NET app. An artist can never approve/verify/(de)activate themselves;
 * every action here is reached only through the admin-gated route group.
 */
class ArtistsController extends Controller
{
    private const PHOTO_SUBFOLDER = 'artists';

    private const MAX_SOCIAL_LINKS = 4;

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(Request $request): View
    {
        $pageSize = 20;
        $page = max(1, (int) $request->integer('page', 1));

        $query = Artist::query()->with('user');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ArtistName', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('Email', 'like', "%{$search}%")->orWhere('UserName', 'like', "%{$search}%"));
            });
        }

        $filter = $request->query('filter');
        match ($filter) {
            'Pending' => $query->where('AccountStatus', Artist::STATUS_PENDING),
            'Verified' => $query->where('IsVerified', true),
            'Active' => $query->where('AccountStatus', Artist::STATUS_ACTIVE),
            'Inactive' => $query->where('AccountStatus', Artist::STATUS_INACTIVE),
            'Rejected' => $query->where('AccountStatus', Artist::STATUS_REJECTED),
            default => null,
        };

        $total = (clone $query)->count();
        $artists = $query->withCount('artworks as ArtworkCount')
            ->orderByDesc('CreatedAt')
            ->forPage($page, $pageSize)
            ->get();

        return view('admin.artists.index', [
            'artists' => $artists,
            'search' => $search ?: null,
            'filter' => $filter,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    public function show(int $id): View
    {
        $artist = Artist::query()->with(['user', 'socialLinks'])->find($id);
        if (! $artist) {
            abort(404);
        }
        $artworks = Artwork::query()->where('ArtistId', $id)->orderByDesc('CreatedAt')->get();

        return view('admin.artists.show', ['artist' => $artist, 'artworks' => $artworks]);
    }

    public function create(): View
    {
        $socialLinks = array_fill(0, self::MAX_SOCIAL_LINKS, ['platform' => '', 'url' => '']);

        return view('admin.artists.form', [
            'artist' => null,
            'user' => null,
            'socialLinks' => $socialLinks,
            'specChecked' => [],
            'specOther' => null,
            'medChecked' => [],
            'medOther' => null,
            'provinces' => ArtistLookupData::PROVINCES,
            'specializations' => ArtistLookupData::SPECIALIZATIONS,
            'mediums' => ArtistLookupData::MEDIUMS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateForm($request, isCreate: true);

        if (AspNetUser::query()->where('NormalizedEmail', mb_strtoupper($data['Email']))->exists()) {
            return back()->withErrors(['Email' => 'An account with this email already exists.'])->withInput();
        }
        if (Artist::query()->where('ArtistName', trim($data['ArtistName']))->exists()) {
            return back()->withErrors(['ArtistName' => 'An artist with this display name already exists.'])->withInput();
        }

        $profilePath = null;
        if ($request->hasFile('ProfilePhoto')) {
            $upload = $this->fileStorage->savePublicImage($request->file('ProfilePhoto'), self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['ProfilePhoto' => $upload->error])->withInput();
            }
            $profilePath = $upload->storedPath;
        }

        $userId = (string) Str::uuid();
        AspNetUser::query()->create([
            'Id' => $userId,
            'IsActive' => true,
            'CreatedAt' => now(),
            'UserName' => $data['Email'],
            'NormalizedUserName' => mb_strtoupper($data['Email']),
            'Email' => $data['Email'],
            'NormalizedEmail' => mb_strtoupper($data['Email']),
            'EmailConfirmed' => true,
            'PasswordHash' => Hash::make($data['Password']),
            'SecurityStamp' => (string) Str::uuid(),
            'ConcurrencyStamp' => (string) Str::uuid(),
            'PhoneNumber' => ! empty($data['ContactPhone']) ? trim($data['ContactPhone']) : null,
            'PhoneNumberConfirmed' => false,
            'TwoFactorEnabled' => false,
            'LockoutEnabled' => true,
            'AccessFailedCount' => 0,
            'DisplayName' => trim($data['ArtistName']),
        ]);

        $artistRole = AspNetRole::query()->where('NormalizedName', 'ARTIST')->first();
        if ($artistRole) {
            DB::table('aspnetuserroles')->insert(['UserId' => $userId, 'RoleId' => $artistRole->Id]);
        }

        $accountStatus = (int) $data['AccountStatus'];
        $isVerified = $request->boolean('IsVerified');

        $artist = Artist::query()->create([
            'UserId' => $userId,
            'ArtistName' => trim($data['ArtistName']),
            'FullName' => trim($data['FullName']),
            'Slug' => $this->uniqueSlug($data['ArtistName']),
            'ProfilePhotoPath' => $profilePath,
            'Biography' => ! empty($data['Biography']) ? trim($data['Biography']) : null,
            'ArtistStatement' => ! empty($data['ArtistStatement']) ? trim($data['ArtistStatement']) : null,
            'Specialization' => ArtistLookupData::combineSelections($data['Specializations'] ?? [], $data['SpecializationOther'] ?? null),
            'PreferredMedium' => ArtistLookupData::combineSelections($data['Mediums'] ?? [], $data['MediumOther'] ?? null),
            'Municipality' => ! empty($data['Municipality']) ? trim($data['Municipality']) : null,
            'Province' => ! empty($data['Province']) ? trim($data['Province']) : null,
            'DateOfBirth' => $data['DateOfBirth'] ?? null,
            'YearsActive' => $data['YearsActive'] ?? null,
            'IsProfilePublic' => $request->boolean('IsProfilePublic'),
            'IsFeatured' => $request->boolean('IsFeatured'),
            'FeaturedOrder' => 0,
            'AccountStatus' => $accountStatus,
            'IsVerified' => $isVerified,
            'VerifiedDate' => $isVerified ? now() : null,
            'VerifiedByUserId' => $isVerified ? auth()->id() : null,
            'ApprovedDate' => $accountStatus === Artist::STATUS_ACTIVE ? now() : null,
            'CreatedAt' => now(),
        ]);

        $this->saveSocialLinks($artist, $request);

        return redirect()->route('admin.artists.show', $artist->Id)->with('artistMessage', 'Artist profile has been successfully created.');
    }

    public function edit(int $id): View
    {
        $artist = Artist::query()->with(['user', 'socialLinks'])->find($id);
        if (! $artist) {
            abort(404);
        }

        [$specChecked, $specOther] = ArtistLookupData::parseSelections(ArtistLookupData::SPECIALIZATIONS, $artist->Specialization);
        [$medChecked, $medOther] = ArtistLookupData::parseSelections(ArtistLookupData::MEDIUMS, $artist->PreferredMedium);

        $socialLinks = $artist->socialLinks->map(fn (ArtistSocialLink $l) => ['platform' => $l->Platform, 'url' => $l->Url])->values()->all();
        while (count($socialLinks) < self::MAX_SOCIAL_LINKS) {
            $socialLinks[] = ['platform' => '', 'url' => ''];
        }

        return view('admin.artists.form', [
            'artist' => $artist,
            'user' => $artist->user,
            'socialLinks' => $socialLinks,
            'specChecked' => $specChecked,
            'specOther' => $specOther,
            'medChecked' => $medChecked,
            'medOther' => $medOther,
            'provinces' => ArtistLookupData::PROVINCES,
            'specializations' => ArtistLookupData::SPECIALIZATIONS,
            'mediums' => ArtistLookupData::MEDIUMS,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $artist = Artist::query()->with('user')->find($id);
        if (! $artist) {
            abort(404);
        }

        $data = $this->validateForm($request, isCreate: false);

        $artist->ArtistName = trim($data['ArtistName']);
        $artist->FullName = trim($data['FullName']);
        $artist->Biography = ! empty($data['Biography']) ? trim($data['Biography']) : null;
        $artist->ArtistStatement = ! empty($data['ArtistStatement']) ? trim($data['ArtistStatement']) : null;
        $artist->Specialization = ArtistLookupData::combineSelections($data['Specializations'] ?? [], $data['SpecializationOther'] ?? null);
        $artist->PreferredMedium = ArtistLookupData::combineSelections($data['Mediums'] ?? [], $data['MediumOther'] ?? null);
        $artist->Municipality = ! empty($data['Municipality']) ? trim($data['Municipality']) : null;
        $artist->Province = ! empty($data['Province']) ? trim($data['Province']) : null;
        $artist->DateOfBirth = $data['DateOfBirth'] ?? null;
        $artist->YearsActive = $data['YearsActive'] ?? null;
        $artist->IsProfilePublic = $request->boolean('IsProfilePublic');
        $artist->IsFeatured = $request->boolean('IsFeatured');
        $artist->FeaturedOrder = $data['FeaturedOrder'] ?? 0;
        $artist->AccountStatus = (int) $data['AccountStatus'];
        $artist->IsVerified = $request->boolean('IsVerified');
        $artist->UpdatedAt = now();

        if ($artist->user) {
            $artist->user->forceFill(['PhoneNumber' => ! empty($data['ContactPhone']) ? trim($data['ContactPhone']) : null])->save();
        }

        if ($request->boolean('RemoveProfilePhoto') && ! $request->hasFile('ProfilePhoto')) {
            $this->fileStorage->deletePublicImage($artist->ProfilePhotoPath);
            $artist->ProfilePhotoPath = null;
        }
        if ($request->hasFile('ProfilePhoto')) {
            $upload = $this->fileStorage->savePublicImage($request->file('ProfilePhoto'), self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['ProfilePhoto' => $upload->error])->withInput();
            }
            $this->fileStorage->deletePublicImage($artist->ProfilePhotoPath);
            $artist->ProfilePhotoPath = $upload->storedPath;
        }

        $artist->save();
        $this->saveSocialLinks($artist, $request);

        return redirect()->route('admin.artists.show', $id)->with('artistMessage', 'Artist profile has been successfully updated.');
    }

    public function approve(int $id): RedirectResponse
    {
        $artist = Artist::query()->find($id);
        if ($artist) {
            $artist->IsVerified = true;
            $artist->VerifiedDate = now();
            $artist->VerifiedByUserId = auth()->id();
            $artist->AccountStatus = Artist::STATUS_ACTIVE;
            $artist->ApprovedDate = now();
            $artist->UpdatedAt = now();
            $artist->save();
        }

        return redirect()->route('admin.artists.show', $id);
    }

    public function reject(int $id): RedirectResponse
    {
        $artist = Artist::query()->find($id);
        if ($artist) {
            $artist->AccountStatus = Artist::STATUS_REJECTED;
            $artist->IsVerified = false;
            $artist->UpdatedAt = now();
            $artist->save();
        }

        return redirect()->route('admin.artists.show', $id);
    }

    public function activate(int $id): RedirectResponse
    {
        $artist = Artist::query()->find($id);
        if ($artist) {
            $artist->AccountStatus = Artist::STATUS_ACTIVE;
            $artist->UpdatedAt = now();
            $artist->save();
        }

        return redirect()->route('admin.artists.show', $id);
    }

    public function deactivate(int $id): RedirectResponse
    {
        $artist = Artist::query()->find($id);
        if ($artist) {
            $artist->AccountStatus = Artist::STATUS_INACTIVE;
            $artist->UpdatedAt = now();
            $artist->save();
        }

        return redirect()->route('admin.artists.show', $id);
    }

    public function toggleFeatured(int $id): RedirectResponse
    {
        $artist = Artist::query()->find($id);
        if ($artist) {
            $artist->IsFeatured = ! $artist->IsFeatured;
            $artist->UpdatedAt = now();
            $artist->save();
        }

        return redirect()->route('admin.artists.show', $id);
    }

    /**
     * Permanently removes the artist account, its artworks, and every
     * associated file. Requires retyping the artist's exact display name.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $artist = Artist::query()->with(['user', 'artworks.additionalImages'])->find($id);
        if (! $artist) {
            abort(404);
        }

        if (trim((string) $request->input('confirmName')) !== $artist->ArtistName) {
            return redirect()->route('admin.artists.show', $id)
                ->with('artistError', 'The typed name did not match. The artist profile was NOT deleted.');
        }

        $this->fileStorage->deletePublicImage($artist->ProfilePhotoPath);
        foreach ($artist->artworks as $artwork) {
            $this->fileStorage->deletePublicImage($artwork->ImagePath, $artwork->ThumbnailPath);
            foreach ($artwork->additionalImages as $img) {
                $this->fileStorage->deletePublicImage($img->ImagePath, $img->ThumbnailPath);
            }
        }

        // Deleting the Identity user cascades at the DB level: User -> Artist ->
        // Artworks -> ArtworkImages, and ArtistSocialLinks - no orphaned rows.
        if ($artist->user) {
            $artist->user->delete();
        } else {
            $artist->delete();
        }

        return redirect()->route('admin.artists.index')
            ->with('artistMessage', 'The artist profile and all associated data have been permanently deleted.');
    }

    private function saveSocialLinks(Artist $artist, Request $request): void
    {
        ArtistSocialLink::query()->where('ArtistId', $artist->Id)->delete();
        foreach ((array) $request->input('SocialLinks', []) as $link) {
            $platform = trim((string) ($link['platform'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            if ($platform !== '' && $url !== '') {
                ArtistSocialLink::query()->create(['ArtistId' => $artist->Id, 'Platform' => $platform, 'Url' => $url]);
            }
        }
    }

    private function validateForm(Request $request, bool $isCreate): array
    {
        $rules = [
            'ArtistName' => ['required', 'max:150'],
            'FullName' => ['required', 'max:150'],
            'Biography' => ['nullable', 'max:2000'],
            'ArtistStatement' => ['nullable', 'max:2000'],
            'Specializations' => ['nullable', 'array'],
            'SpecializationOther' => ['nullable', 'max:150'],
            'Mediums' => ['nullable', 'array'],
            'MediumOther' => ['nullable', 'max:150'],
            'Municipality' => ['nullable', 'max:150'],
            'Province' => ['nullable', 'max:150'],
            'DateOfBirth' => ['nullable', 'date', 'before_or_equal:today'],
            'ContactPhone' => ['nullable', 'max:30'],
            'YearsActive' => ['nullable', 'integer', 'min:0', 'max:100'],
            'FeaturedOrder' => ['nullable', 'integer'],
            'AccountStatus' => ['required', 'integer', 'between:0,3'],
            'ProfilePhoto' => ['nullable', 'image', 'max:10240'],
        ];

        if ($isCreate) {
            $rules['Email'] = ['required', 'email', 'max:200'];
            $rules['Password'] = ['required', 'min:8', 'confirmed'];
        }

        return $request->validate($rules);
    }

    private function uniqueSlug(string $name): string
    {
        $base = SlugHelper::generateSlug($name) ?: 'artist';
        $slug = $base;
        $suffix = 1;
        while (Artist::query()->where('Slug', $slug)->exists()) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
