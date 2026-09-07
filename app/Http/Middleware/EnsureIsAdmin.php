<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Only Super Admin / Admin accounts may reach the Admin area. */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->isAdmin())) {
            abort(403);
        }

        return $next($request);
    }
}
