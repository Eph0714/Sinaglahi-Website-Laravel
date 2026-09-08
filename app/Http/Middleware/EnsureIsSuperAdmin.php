<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * User Management and Admin Roles & Permissions require the Super Admin
 * role outright, not merely a granted permission - a regular Admin never
 * reaches these controllers at all, matching the .NET app's
 * [Authorize(Roles = Roles.SuperAdmin)] guard.
 */
class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
