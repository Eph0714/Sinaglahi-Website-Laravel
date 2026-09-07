<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArtworksController extends Controller
{
    /** Base filter: only artworks that have cleared moderation and belong to a
     * visible artist - mirrors PublicCatalogService.VisibleArtworks in the .NET app. */
    private function visibleArtworks()
    {
        return Artwork::query()->published()
            ->whereHas('artist', fn ($q) => $q->publiclyVisible());
    }

    /** Mirrors PublicCatalogService.SearchArtworksAsync in the .NET app. */
    public function index(Request $request): View
    {
        $pageSize = 12;
        $page = max(1, (int) $request->integer('page', 1));

        $query = $this->visibleArtworks()->with('artist');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                    ->orWhereHas('artist', fn ($a) => $a->where('ArtistName', 'like', "%{$search}%"));
            });
        }
        if ($artistId = $request->query('artistId')) {
            $query->where('ArtistId', $artistId);
        }
        if ($medium = $request->query('medium')) {
            $query->where('Medium', $medium);
        }
        if ($year = $request->query('year')) {
            $query->where('Year', $year);
        }
        if ($categoryId = $request->query('categoryId')) {
            $query->where('CategoryId', $categoryId);
        }

        $total = (clone $query)->count();
        $artworks = $query->orderByDesc('ApprovedAt')->forPage($page, $pageSize)->get();

        $mediums = $this->visibleArtworks()->distinct()->orderBy('Medium')->pluck('Medium');
        $years = $this->visibleArtworks()->distinct()->orderByDesc('Year')->pluck('Year');
        $categories = Category::query()->active()->orderBy('DisplayOrder')->get();

        return view('artworks.index', [
            'artworks' => $artworks,
            'search' => $search ?: null,
            'artistId' => $artistId,
            'medium' => $medium,
            'year' => $year,
            'categoryId' => $categoryId,
            'mediumOptions' => $mediums,
            'yearOptions' => $years,
            'categoryOptions' => $categories,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    /** Mirrors PublicCatalogService.GetArtworkDetailAsync in the .NET app. */
    public function show(string $slug): View
    {
        $artwork = $this->visibleArtworks()->with('artist')->where('Slug', $slug)->first();

        if (! $artwork) {
            throw new NotFoundHttpException;
        }

        $moreFromArtist = $this->visibleArtworks()
            ->where('ArtistId', $artwork->ArtistId)
            ->where('Id', '!=', $artwork->Id)
            ->orderByDesc('ApprovedAt')
            ->take(4)
            ->get();

        return view('artworks.show', ['artwork' => $artwork, 'moreFromArtist' => $moreFromArtist]);
    }
}
