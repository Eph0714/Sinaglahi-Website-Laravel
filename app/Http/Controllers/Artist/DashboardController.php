<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Mirrors Areas/Artist/Controllers/DashboardController in the .NET app. */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $artist = $request->attributes->get('artist');
        $artworks = Artwork::query()->where('ArtistId', $artist->Id);

        return view('artist.dashboard.index', [
            'artist' => $artist,
            'totalArtworks' => (clone $artworks)->count(),
            'publishedArtworks' => (clone $artworks)->where('Status', Artwork::STATUS_PUBLISHED)->count(),
            'pendingArtworks' => (clone $artworks)->where('Status', Artwork::STATUS_PENDING_REVIEW)->count(),
            'rejectedArtworks' => (clone $artworks)->where('Status', Artwork::STATUS_REJECTED)->count(),
        ]);
    }
}
