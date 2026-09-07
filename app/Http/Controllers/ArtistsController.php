<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArtistsController extends Controller
{
    /** Mirrors PublicCatalogService.SearchArtistsAsync in the .NET app. */
    public function index(Request $request): View
    {
        $pageSize = 12;
        $page = max(1, (int) $request->integer('page', 1));

        $query = Artist::query()->publiclyVisible();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ArtistName', 'like', "%{$search}%")
                    ->orWhere('Specialization', 'like', "%{$search}%");
            });
        }
        if ($municipality = $request->query('municipality')) {
            $query->where('Municipality', $municipality);
        }
        if ($specialization = $request->query('specialization')) {
            $query->where('Specialization', $specialization);
        }

        $total = (clone $query)->count();
        $artists = $query->orderBy('ArtistName')->forPage($page, $pageSize)->get();

        $municipalities = Artist::query()->publiclyVisible()->whereNotNull('Municipality')
            ->distinct()->orderBy('Municipality')->pluck('Municipality');
        $specializations = Artist::query()->publiclyVisible()->whereNotNull('Specialization')
            ->distinct()->orderBy('Specialization')->pluck('Specialization');

        return view('artists.index', [
            'artists' => $artists,
            'search' => $search ?: null,
            'municipality' => $municipality,
            'specialization' => $specialization,
            'municipalityOptions' => $municipalities,
            'specializationOptions' => $specializations,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    /** Mirrors PublicCatalogService.GetArtistProfileAsync in the .NET app. */
    public function show(string $slug): View
    {
        $artist = Artist::query()->publiclyVisible()
            ->with('socialLinks')
            ->where('Slug', $slug)
            ->first();

        if (! $artist) {
            throw new NotFoundHttpException;
        }

        $artworks = $artist->artworks()->published()->orderByDesc('ApprovedAt')->get();

        return view('artists.show', ['artist' => $artist, 'artworks' => $artworks]);
    }
}
