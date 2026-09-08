<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtMedium;
use App\Models\Artwork;
use App\Models\Category;
use App\Services\ArtMediumHelper;
use App\Services\FileStorageService;
use App\Services\SlugHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Artwork Management module - mirrors Areas/Admin/Controllers/ArtworksController
 * in the .NET app. The "additional gallery photos per artwork" (ManageImages)
 * sub-feature is not ported yet - noted as a deliberate gap.
 */
class ArtworksController extends Controller
{
    private const PHOTO_SUBFOLDER = 'artworks';

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(Request $request): View
    {
        $pageSize = 24;
        $page = max(1, (int) $request->integer('page', 1));

        $query = Artwork::query()->with('artist');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                    ->orWhereHas('artist', fn ($a) => $a->where('ArtistName', 'like', "%{$search}%"));
            });
        }

        $filter = $request->query('filter');
        match ($filter) {
            'Pending' => $query->where('Status', Artwork::STATUS_PENDING_REVIEW),
            'Published' => $query->where('Status', Artwork::STATUS_PUBLISHED),
            'Featured' => $query->where('IsFeatured', true),
            'Rejected' => $query->where('Status', Artwork::STATUS_REJECTED),
            'Application' => $query->where('Source', 1), // MembershipApplication
            default => null,
        };

        $total = (clone $query)->count();
        $artworks = $query->orderByDesc('SubmittedAt')->forPage($page, $pageSize)->get();

        return view('admin.artworks.index', [
            'artworks' => $artworks,
            'search' => $search ?: null,
            'filter' => $filter,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    public function show(int $id): View
    {
        $artwork = Artwork::query()->with(['artist', 'category', 'additionalImages'])->find($id);
        if (! $artwork) {
            abort(404);
        }

        return view('admin.artworks.show', ['artwork' => $artwork]);
    }

    public function create(Request $request): View
    {
        return view('admin.artworks.form', [
            'artwork' => null,
            'artistId' => $request->integer('artistId') ?: null,
            'categoryOptions' => $this->categoryOptions(),
            'artistOptions' => Artist::query()->orderBy('ArtistName')->get(),
            'mediumOptions' => ArtMediumHelper::getOptionsForEdit(null),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, isCreate: true);
        $medium = $this->validateMedium($request, $data);

        if (! $request->hasFile('Image')) {
            return back()->withErrors(['Image' => 'Please choose an image for this artwork.'])->withInput();
        }
        if (! Artist::query()->where('Id', $data['ArtistId'])->exists()) {
            return back()->withErrors(['ArtistId' => 'Please select a valid artist.'])->withInput();
        }
        if (! $medium) {
            return back()->withErrors(['MediumId' => 'Please select a valid medium.'])->withInput();
        }

        $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::PHOTO_SUBFOLDER);
        if (! $upload->success) {
            return back()->withErrors(['Image' => $upload->error])->withInput();
        }

        $status = (int) $data['Status'];
        $artwork = Artwork::query()->create([
            'ArtistId' => $data['ArtistId'],
            'Title' => trim($data['Title']),
            'MediumId' => $medium->Id,
            'CustomMedium' => $medium->IsOtherOption ? trim((string) $data['CustomMedium']) : null,
            'Medium' => ArtMediumHelper::composeDisplayMedium($medium->Name, $medium->IsOtherOption, $data['CustomMedium'] ?? null),
            'Size' => trim($data['Size']),
            'Year' => $data['Year'],
            'Description' => trim($data['Description']),
            'CategoryId' => $data['CategoryId'] ?? null,
            'Price' => $data['Price'] ?? null,
            'IsAvailable' => $request->boolean('IsAvailable'),
            'IsFeatured' => $request->boolean('IsFeatured'),
            'FeaturedOrder' => 0,
            'ImagePath' => $upload->storedPath,
            'Slug' => $this->uniqueSlug($data['Title']),
            'Status' => $status,
            'Source' => 2, // AdminEntry
            'SubmittedAt' => now(),
            'ApprovedAt' => in_array($status, [Artwork::STATUS_APPROVED, Artwork::STATUS_PUBLISHED], true) ? now() : null,
            'ApprovedByUserId' => in_array($status, [Artwork::STATUS_APPROVED, Artwork::STATUS_PUBLISHED], true) ? auth()->id() : null,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('admin.artworks.show', $artwork->Id)->with('artworkMessage', 'Artwork has been successfully created.');
    }

    public function edit(int $id): View
    {
        $artwork = Artwork::query()->with('artist')->find($id);
        if (! $artwork) {
            abort(404);
        }

        $effectiveMediumId = $artwork->MediumId;
        if (! $effectiveMediumId) {
            $other = ArtMediumHelper::getOtherOption();
            $effectiveMediumId = $other?->Id;
        }

        return view('admin.artworks.form', [
            'artwork' => $artwork,
            'artistId' => null,
            'categoryOptions' => $this->categoryOptions(),
            'artistOptions' => Artist::query()->orderBy('ArtistName')->get(),
            'mediumOptions' => ArtMediumHelper::getOptionsForEdit($effectiveMediumId),
            'effectiveMediumId' => $effectiveMediumId,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $artwork = Artwork::query()->with('artist')->find($id);
        if (! $artwork) {
            abort(404);
        }

        $data = $this->validated($request, isCreate: false);
        $medium = $this->validateMedium($request, $data);
        if (! $medium) {
            return back()->withErrors(['MediumId' => 'Please select a valid medium.'])->withInput();
        }

        if (! empty($data['ArtistId']) && (int) $data['ArtistId'] !== (int) $artwork->ArtistId) {
            if (! Artist::query()->where('Id', $data['ArtistId'])->exists()) {
                return back()->withErrors(['ArtistId' => 'Please select a valid artist.'])->withInput();
            }
            $artwork->ArtistId = $data['ArtistId'];
        }

        $artwork->Title = trim($data['Title']);
        $artwork->MediumId = $medium->Id;
        $artwork->CustomMedium = $medium->IsOtherOption ? trim((string) $data['CustomMedium']) : null;
        $artwork->Medium = ArtMediumHelper::composeDisplayMedium($medium->Name, $medium->IsOtherOption, $data['CustomMedium'] ?? null);
        $artwork->Size = trim($data['Size']);
        $artwork->Year = $data['Year'];
        $artwork->Description = trim($data['Description']);
        $artwork->CategoryId = $data['CategoryId'] ?? null;
        $artwork->Price = $data['Price'] ?? null;
        $artwork->IsAvailable = $request->boolean('IsAvailable');
        $artwork->IsFeatured = $request->boolean('IsFeatured');
        $artwork->Status = (int) $data['Status'];
        $artwork->UpdatedAt = now();

        if ($request->hasFile('Image')) {
            $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['Image' => $upload->error])->withInput();
            }
            $old = $artwork->ImagePath;
            $oldThumb = $artwork->ThumbnailPath;
            $artwork->ImagePath = $upload->storedPath;
            $artwork->ThumbnailPath = null;
            $this->fileStorage->deletePublicImage($old, $oldThumb);
        }

        $artwork->save();

        return redirect()->route('admin.artworks.show', $id)->with('artworkMessage', 'Artwork details and photo have been successfully updated.');
    }

    public function approve(int $id): RedirectResponse
    {
        $artwork = Artwork::query()->find($id);
        if ($artwork) {
            $artwork->Status = Artwork::STATUS_APPROVED;
            $artwork->ApprovedAt = now();
            $artwork->ApprovedByUserId = auth()->id();
            $artwork->UpdatedAt = now();
            $artwork->save();
        }

        return redirect()->route('admin.artworks.show', $id);
    }

    public function reject(int $id): RedirectResponse
    {
        $artwork = Artwork::query()->find($id);
        if ($artwork) {
            $artwork->Status = Artwork::STATUS_REJECTED;
            $artwork->UpdatedAt = now();
            $artwork->save();
        }

        return redirect()->route('admin.artworks.show', $id);
    }

    public function publish(int $id): RedirectResponse
    {
        $artwork = Artwork::query()->find($id);
        if ($artwork) {
            $artwork->Status = Artwork::STATUS_PUBLISHED;
            $artwork->ApprovedAt ??= now();
            $artwork->ApprovedByUserId ??= auth()->id();
            $artwork->UpdatedAt = now();
            $artwork->save();
        }

        return redirect()->route('admin.artworks.show', $id);
    }

    public function unpublish(int $id): RedirectResponse
    {
        $artwork = Artwork::query()->find($id);
        if ($artwork) {
            $artwork->Status = Artwork::STATUS_ARCHIVED;
            $artwork->UpdatedAt = now();
            $artwork->save();
        }

        return redirect()->route('admin.artworks.show', $id);
    }

    public function toggleFeatured(int $id): RedirectResponse
    {
        $artwork = Artwork::query()->find($id);
        if ($artwork) {
            $artwork->IsFeatured = ! $artwork->IsFeatured;
            $artwork->UpdatedAt = now();
            $artwork->save();
        }

        return redirect()->route('admin.artworks.show', $id);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $artwork = Artwork::query()->with('additionalImages')->find($id);
        if (! $artwork) {
            abort(404);
        }

        if (trim((string) $request->input('confirmTitle')) !== $artwork->Title) {
            return redirect()->route('admin.artworks.show', $id)
                ->with('artworkError', 'The typed title did not match. The artwork was NOT deleted.');
        }

        $this->fileStorage->deletePublicImage($artwork->ImagePath, $artwork->ThumbnailPath);
        foreach ($artwork->additionalImages as $img) {
            $this->fileStorage->deletePublicImage($img->ImagePath, $img->ThumbnailPath);
        }
        $artwork->delete();

        return redirect()->route('admin.artworks.index')->with('artworkMessage', 'The artwork and associated files have been permanently deleted.');
    }

    private function categoryOptions(): Collection
    {
        return Category::query()->active()->orderBy('DisplayOrder')->get();
    }

    private function validated(Request $request, bool $isCreate): array
    {
        $rules = [
            'Title' => ['required', 'max:200'],
            'MediumId' => ['required', 'integer'],
            'CustomMedium' => ['nullable', 'max:200'],
            'Size' => ['required', 'max:100'],
            'Year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'Description' => ['required', 'max:4000'],
            'CategoryId' => ['nullable', 'integer'],
            'Price' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'IsAvailable' => ['nullable'],
            'IsFeatured' => ['nullable'],
            'Status' => ['required', 'integer', 'between:0,5'],
            'Image' => ['nullable', 'image', 'max:10240'],
            'ArtistId' => [$isCreate ? 'required' : 'nullable', 'integer'],
        ];

        return $request->validate($rules);
    }

    private function validateMedium(Request $request, array $data): ?ArtMedium
    {
        $medium = ArtMedium::query()->find($data['MediumId']);
        if (! $medium) {
            return null;
        }
        if ($medium->IsOtherOption && trim((string) ($data['CustomMedium'] ?? '')) === '') {
            abort(back()->withErrors(['CustomMedium' => 'Please specify the medium.'])->withInput());
        }

        return $medium;
    }

    private function uniqueSlug(string $title): string
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
