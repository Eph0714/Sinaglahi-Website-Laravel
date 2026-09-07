<?php

namespace App\Http\Middleware;

use App\Models\Artist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Only accounts with an Artist profile may reach the Artist self-service area. */
class EnsureIsArtist
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $artist = $user ? Artist::query()->where('UserId', $user->Id)->first() : null;

        if (! $artist) {
            abort(403);
        }

        $request->attributes->set('artist', $artist);

        return $next($request);
    }
}
