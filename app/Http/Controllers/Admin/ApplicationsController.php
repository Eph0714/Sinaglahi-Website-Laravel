<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationArtwork;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatusHistory;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\AspNetRole;
use App\Models\AspNetUser;
use App\Models\MembershipApplication;
use App\Services\FileStorageService;
use App\Services\SlugHelper;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Membership Applications module - mirrors Areas/Admin/Controllers/
 * ApplicationsController in the .NET app: review, status changes, notes,
 * and converting an approved application into an artist account.
 */
class ApplicationsController extends Controller
{
    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(Request $request): View
    {
        $pageSize = 20;
        $page = max(1, (int) $request->integer('page', 1));

        $query = MembershipApplication::query()->where('Status', '!=', MembershipApplication::STATUS_DRAFT);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('FullName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%")
                    ->orWhere('ContactNumber', 'like', "%{$search}%")
                    ->orWhere('ReferenceNumber', 'like', "%{$search}%");
            });
        }
        if ($status = $request->query('status')) {
            $query->where('Status', $status);
        }
        if ($dateFrom = $request->query('dateFrom')) {
            $query->where('SubmittedAt', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('dateTo')) {
            $query->where('SubmittedAt', '<=', Carbon::parse($dateTo)->addDay());
        }

        $total = (clone $query)->count();
        $applications = $query->withCount('artworks as ArtworkCount')
            ->orderByDesc('SubmittedAt')->forPage($page, $pageSize)->get();

        return view('admin.applications.index', [
            'applications' => $applications,
            'search' => $search ?: null,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    public function show(int $id): View
    {
        $app = MembershipApplication::query()->find($id);
        if (! $app) {
            abort(404);
        }

        return view('admin.applications.show', [
            'app' => $app,
            'artworks' => ApplicationArtwork::query()->where('MembershipApplicationId', $id)->orderBy('Id')->get(),
            'notes' => ApplicationNote::query()->where('MembershipApplicationId', $id)->orderByDesc('CreatedAt')->get(),
            'statusHistory' => ApplicationStatusHistory::query()->where('MembershipApplicationId', $id)->orderByDesc('ChangedAt')->get(),
            'alreadyConverted' => (bool) $app->ConvertedArtistId,
        ]);
    }

    public function profilePhoto(int $id)
    {
        $app = MembershipApplication::query()->find($id);
        if (! $app || ! $app->ProfilePhotoPath) {
            abort(404);
        }
        $result = $this->fileStorage->readPrivateImage($app->ProfilePhotoPath);
        if (! $result) {
            abort(404);
        }

        return response($result['bytes'])->header('Content-Type', $result['contentType']);
    }

    public function artworkImage(int $id)
    {
        $artwork = ApplicationArtwork::query()->find($id);
        if (! $artwork) {
            abort(404);
        }
        $result = $this->fileStorage->readPrivateImage($artwork->ThumbnailPath ?? $artwork->ImagePath);
        if (! $result) {
            abort(404);
        }

        return response($result['bytes'])->header('Content-Type', $result['contentType']);
    }

    private function recordStatusHistory(int $applicationId, int $status, ?string $remarks): void
    {
        ApplicationStatusHistory::query()->create([
            'MembershipApplicationId' => $applicationId,
            'Status' => $status,
            'Remarks' => $remarks,
            'ChangedByUserId' => auth()->id(),
            'ChangedAt' => now(),
        ]);
    }

    public function markUnderReview(int $id): RedirectResponse
    {
        $app = MembershipApplication::query()->find($id);
        if ($app && in_array((int) $app->Status, [MembershipApplication::STATUS_PENDING_REVIEW, MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED], true)) {
            $app->Status = MembershipApplication::STATUS_UNDER_REVIEW;
            $app->ReviewedByUserId = auth()->id();
            $app->ReviewedAt = now();
            $app->UpdatedAt = now();
            $app->save();
            $this->recordStatusHistory($app->Id, $app->Status, 'Application is now being evaluated.');
        }

        return redirect()->route('admin.applications.show', $id);
    }

    public function requestInfo(Request $request, int $id): RedirectResponse
    {
        $app = MembershipApplication::query()->find($id);
        if (! $app) {
            abort(404);
        }

        $note = trim((string) $request->input('note'));

        $app->Status = MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED;
        $app->ReviewedByUserId = auth()->id();
        $app->ReviewedAt = now();
        $app->UpdatedAt = now();
        $app->save();

        if ($note !== '') {
            ApplicationNote::query()->create([
                'MembershipApplicationId' => $id,
                'Note' => $note,
                'IsApplicantVisible' => true,
                'CreatedByUserId' => auth()->id(),
                'CreatedAt' => now(),
            ]);
        }

        $this->recordStatusHistory($id, $app->Status, $note !== '' ? $note : 'Additional information required.');

        return redirect()->route('admin.applications.show', $id)->with('applicationMessage', 'Additional information requested.');
    }

    public function approve(int $id): RedirectResponse
    {
        $app = MembershipApplication::query()->find($id);
        if (! $app) {
            abort(404);
        }

        $app->Status = MembershipApplication::STATUS_APPROVED;
        $app->ReviewedByUserId = auth()->id();
        $app->ReviewedAt = now();
        $app->UpdatedAt = now();
        $app->save();
        $this->recordStatusHistory($app->Id, $app->Status, 'Membership approved.');

        return redirect()->route('admin.applications.show', $id)
            ->with('applicationMessage', 'Application approved. You may now convert it into an artist account.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $reason = trim((string) $request->input('reason'));
        if ($reason === '') {
            return redirect()->route('admin.applications.show', $id)
                ->with('applicationError', 'Please provide a reason for rejecting this application.');
        }

        $app = MembershipApplication::query()->find($id);
        if (! $app) {
            abort(404);
        }

        $app->Status = MembershipApplication::STATUS_REJECTED;
        $app->ReviewedByUserId = auth()->id();
        $app->ReviewedAt = now();
        $app->UpdatedAt = now();
        $app->save();

        ApplicationNote::query()->create([
            'MembershipApplicationId' => $id,
            'Note' => $reason,
            'IsApplicantVisible' => true,
            'CreatedByUserId' => auth()->id(),
            'CreatedAt' => now(),
        ]);
        $this->recordStatusHistory($id, $app->Status, $reason);

        return redirect()->route('admin.applications.show', $id)->with('applicationMessage', 'Application rejected.');
    }

    public function archive(int $id): RedirectResponse
    {
        $app = MembershipApplication::query()->find($id);
        if ($app) {
            $app->Status = MembershipApplication::STATUS_ARCHIVED;
            $app->UpdatedAt = now();
            $app->save();
            $this->recordStatusHistory($app->Id, $app->Status, 'Application archived.');
        }

        return redirect()->route('admin.applications.show', $id);
    }

    public function addNote(Request $request, int $id): RedirectResponse
    {
        $note = trim((string) $request->input('note'));
        if ($note !== '') {
            ApplicationNote::query()->create([
                'MembershipApplicationId' => $id,
                'Note' => $note,
                'IsApplicantVisible' => false,
                'CreatedByUserId' => auth()->id(),
                'CreatedAt' => now(),
            ]);
        }

        return redirect()->route('admin.applications.show', $id);
    }

    /**
     * Creates a login account + Artist profile from an approved application,
     * and copies its artworks over. The generated temporary password is
     * shown once on the confirmation flash for the admin to relay to the
     * applicant (no outbound email is wired up, matching the .NET app).
     */
    public function convertToArtist(Request $request, int $id): RedirectResponse
    {
        $app = MembershipApplication::query()->with('artworks')->find($id);
        if (! $app) {
            abort(404);
        }

        if ($app->ConvertedArtistId) {
            return redirect()->route('admin.applications.show', $id)
                ->with('applicationError', 'This application has already been converted into an artist account.');
        }
        if (AspNetUser::query()->where('NormalizedEmail', mb_strtoupper($app->Email))->exists()) {
            return redirect()->route('admin.applications.show', $id)
                ->with('applicationError', "An account with this applicant's email already exists.");
        }

        $activateImmediately = $request->boolean('activateImmediately');
        $publishArtworks = $request->boolean('publishArtworks');
        $tempPassword = $this->generateTemporaryPassword();

        $userId = (string) Str::uuid();
        AspNetUser::query()->create([
            'Id' => $userId,
            'IsActive' => true,
            'CreatedAt' => now(),
            'UserName' => $app->Email,
            'NormalizedUserName' => mb_strtoupper($app->Email),
            'Email' => $app->Email,
            'NormalizedEmail' => mb_strtoupper($app->Email),
            'EmailConfirmed' => true,
            'PasswordHash' => Hash::make($tempPassword),
            'SecurityStamp' => (string) Str::uuid(),
            'ConcurrencyStamp' => (string) Str::uuid(),
            'PhoneNumber' => $app->ContactNumber,
            'PhoneNumberConfirmed' => false,
            'TwoFactorEnabled' => false,
            'LockoutEnabled' => true,
            'AccessFailedCount' => 0,
        ]);

        $artistRole = AspNetRole::query()->where('NormalizedName', 'ARTIST')->first();
        if ($artistRole) {
            DB::table('aspnetuserroles')->insert(['UserId' => $userId, 'RoleId' => $artistRole->Id]);
        }

        $profilePhotoPath = $app->ProfilePhotoPath
            ? $this->fileStorage->publishPrivateImage($app->ProfilePhotoPath, 'artists')
            : null;

        $artistName = $app->PreferredName ?: $app->FullName;
        $artist = Artist::query()->create([
            'UserId' => $userId,
            'SourceApplicationId' => $app->Id,
            'ArtistName' => $artistName,
            'FullName' => $app->FullName,
            'Slug' => $this->uniqueArtistSlug($artistName),
            'Municipality' => $app->Municipality,
            'Province' => $app->Province,
            'PreferredMedium' => $app->ArtMediums,
            'ProfilePhotoPath' => $profilePhotoPath,
            'IsVerified' => $activateImmediately,
            'VerifiedDate' => $activateImmediately ? now() : null,
            'VerifiedByUserId' => $activateImmediately ? auth()->id() : null,
            'AccountStatus' => $activateImmediately ? Artist::STATUS_ACTIVE : Artist::STATUS_PENDING,
            'ApprovedDate' => $activateImmediately ? now() : null,
            'IsProfilePublic' => true,
            'IsFeatured' => false,
            'FeaturedOrder' => 0,
            'CreatedAt' => now(),
        ]);

        foreach ($app->artworks as $appArtwork) {
            $publicPath = $this->fileStorage->publishPrivateImage($appArtwork->ImagePath, 'artworks');
            if (! $publicPath) {
                continue; // source file missing; skip rather than fail the whole conversion
            }

            Artwork::query()->create([
                'ArtistId' => $artist->Id,
                'SourceApplicationArtworkId' => $appArtwork->Id,
                'Title' => $appArtwork->Title,
                'MediumId' => $appArtwork->MediumId,
                'CustomMedium' => $appArtwork->CustomMedium,
                'Medium' => $appArtwork->Medium,
                'Size' => $appArtwork->Size ?: 'Not specified',
                'Year' => $appArtwork->Year ?: now()->year,
                'Description' => $appArtwork->Description ?: '',
                'CategoryId' => $appArtwork->CategoryId,
                'ImagePath' => $publicPath,
                'Slug' => $this->uniqueArtworkSlug($appArtwork->Title),
                'Status' => $publishArtworks ? Artwork::STATUS_PUBLISHED : Artwork::STATUS_PENDING_REVIEW,
                'Source' => 1, // MembershipApplication
                'IsAvailable' => true,
                'IsFeatured' => false,
                'FeaturedOrder' => 0,
                'SubmittedAt' => $appArtwork->CreatedAt,
                'ApprovedAt' => $publishArtworks ? now() : null,
                'ApprovedByUserId' => $publishArtworks ? auth()->id() : null,
                'CreatedAt' => now(),
            ]);
        }

        $app->ConvertedArtistId = $artist->Id;
        if ((int) $app->Status !== MembershipApplication::STATUS_APPROVED) {
            $app->Status = MembershipApplication::STATUS_APPROVED;
            $app->ReviewedByUserId = auth()->id();
            $app->ReviewedAt = now();
        }
        $app->UpdatedAt = now();
        $app->save();

        return redirect()->route('admin.artists.show', $artist->Id)->with('artistMessage',
            "Artist account created (email: {$app->Email}). Temporary password: {$tempPassword} — please share this with the applicant through a secure channel.");
    }

    private function generateTemporaryPassword(): string
    {
        // Guaranteed to satisfy the app's password rules (upper/lower/digit/length).
        return 'Sg-'.substr(bin2hex(random_bytes(6)), 0, 10).'!1Aa';
    }

    private function uniqueArtistSlug(string $name): string
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

    private function uniqueArtworkSlug(string $title): string
    {
        $base = SlugHelper::generateSlug($title) ?: 'artwork';
        $slug = $base;
        $suffix = 1;
        while (Artwork::query()->where('Slug', $slug)->exists()) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
