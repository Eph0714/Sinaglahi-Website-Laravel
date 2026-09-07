<?php

namespace App\Http\Controllers\Artist;

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
 * "My Artworks" - upload and manage the artist's own artworks. Mirrors
 * Areas/Artist/Controllers/ArtworksController in the .NET app. Every query
 * is scoped by the authenticated artist's own Id.
 */
class ArtworksController extends Controller
{
    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(Request $request): View
    {
        $artist = $request->attributes->get('artist');
        $artworks = Artwork::query()->where('ArtistId', $artist->Id)->orderByDesc('CreatedAt')->get();

        return view('artist.artworks.index', ['artworks' => $artworks]);
    }

    public function create(Request $request): View
    {
        return view('artist.artworks.form', [
            'artwork' => null,
            'categoryOptions' => $this->categoryOptions(),
            'mediumOptions' => ArtMediumHelper::getOptionsForEdit(null),
            'formAction' => route('artist.artworks.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $artist = $request->attributes->get('artist');
        $data = $this->validated($request);
        $medium = $this->validateMedium($request, $data);

        if (! $request->hasFile('Image')) {
            return back()->withErrors(['Image' => 'Please choose an image for this artwork.'])->withInput();
        }
        if (! $medium) {
            return back()->withErrors(['MediumId' => 'Please select a valid medium.'])->withInput();
        }

        $upload = $this->fileStorage->savePublicImage($request->file('Image'), 'artworks');
        if (! $upload->success) {
            return back()->withErrors(['Image' => $upload->error])->withInput();
        }

        Artwork::query()->create([
            'ArtistId' => $artist->Id,
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
            'ImagePath' => $upload->storedPath,
            'Slug' => $this->uniqueSlug($data['Title']),
            'Status' => Artwork::STATUS_PENDING_REVIEW,
            'Source' => 0, // ArtistDashboard
            'IsFeatured' => false,
            'FeaturedOrder' => 0,
            'SubmittedAt' => now(),
            'CreatedAt' => now(),
        ]);

        return redirect()->route('artist.artworks.index')->with('artworkSaved', 'Your artwork has been submitted for review.');
    }

    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $artist = $request->attributes->get('artist');
        $artwork = Artwork::query()->where('Id', $id)->where('ArtistId', $artist->Id)->first();
        if (! $artwork) {
            abort(404);
        }
        if (! $this->canEdit((int) $artwork->Status)) {
            return redirect()->route('artist.artworks.index');
        }

        $effectiveMediumId = $artwork->MediumId;
        if (! $effectiveMediumId) {
            $other = ArtMediumHelper::getOtherOption();
            $effectiveMediumId = $other?->Id;
        }

        return view('artist.artworks.form', [
            'artwork' => $artwork,
            'categoryOptions' => $this->categoryOptions(),
            'mediumOptions' => ArtMediumHelper::getOptionsForEdit($effectiveMediumId),
            'effectiveMediumId' => $effectiveMediumId,
            'formAction' => route('artist.artworks.update', $artwork->Id),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $artist = $request->attributes->get('artist');
        $artwork = Artwork::query()->where('Id', $id)->where('ArtistId', $artist->Id)->first();
        if (! $artwork) {
            abort(404);
        }
        if (! $this->canEdit((int) $artwork->Status)) {
            return redirect()->route('artist.artworks.index');
        }

        $data = $this->validated($request);
        $medium = $this->validateMedium($request, $data);
        if (! $medium) {
            return back()->withErrors(['MediumId' => 'Please select a valid medium.'])->withInput();
        }

        if ($request->hasFile('Image')) {
            $upload = $this->fileStorage->savePublicImage($request->file('Image'), 'artworks');
            if (! $upload->success) {
                return back()->withErrors(['Image' => $upload->error])->withInput();
            }
            $artwork->ImagePath = $upload->storedPath;
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
        $artwork->UpdatedAt = now();

        // Editing a rejected artwork resubmits it for another look.
        if ((int) $artwork->Status === Artwork::STATUS_REJECTED) {
            $artwork->Status = Artwork::STATUS_PENDING_REVIEW;
            $artwork->SubmittedAt = now();
        }

        $artwork->save();

        return redirect()->route('artist.artworks.index')->with('artworkSaved', 'Your artwork has been updated.');
    }

    public function withdraw(Request $request, int $id): RedirectResponse
    {
        $artist = $request->attributes->get('artist');
        $artwork = Artwork::query()->where('Id', $id)->where('ArtistId', $artist->Id)->first();
        if ($artwork && (int) $artwork->Status === Artwork::STATUS_PENDING_REVIEW) {
            $artwork->Status = Artwork::STATUS_DRAFT;
            $artwork->UpdatedAt = now();
            $artwork->save();
        }

        return redirect()->route('artist.artworks.index');
    }

    public function submit(Request $request, int $id): RedirectResponse
    {
        $artist = $request->attributes->get('artist');
        $artwork = Artwork::query()->where('Id', $id)->where('ArtistId', $artist->Id)->first();
        if ($artwork && (int) $artwork->Status === Artwork::STATUS_DRAFT) {
            $artwork->Status = Artwork::STATUS_PENDING_REVIEW;
            $artwork->SubmittedAt = now();
            $artwork->save();
        }

        return redirect()->route('artist.artworks.index');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $artist = $request->attributes->get('artist');
        Artwork::query()->where('Id', $id)->where('ArtistId', $artist->Id)->delete();

        return redirect()->route('artist.artworks.index');
    }

    private function canEdit(int $status): bool
    {
        return in_array($status, [Artwork::STATUS_DRAFT, Artwork::STATUS_PENDING_REVIEW, Artwork::STATUS_REJECTED], true);
    }

    private function categoryOptions(): Collection
    {
        return Category::query()->active()->orderBy('DisplayOrder')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Title' => ['required', 'max:200'],
            'MediumId' => ['required', 'integer'],
            'CustomMedium' => ['nullable', 'max:200'],
            'Size' => ['required', 'max:100'],
            'Year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'Description' => ['required', 'max:4000'],
            'CategoryId' => ['nullable', 'integer'],
            'Price' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'IsAvailable' => ['nullable'],
            'Image' => ['nullable', 'image', 'max:10240'],
        ]);
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
