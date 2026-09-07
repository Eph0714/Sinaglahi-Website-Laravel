<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\AspNetUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Login/logout for both Super-Admin/Admin and Artist accounts - mirrors
 * AccountController in the .NET app (same exact-status messaging).
 */
class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        return view('auth.login', ['returnUrl' => $request->query('returnUrl')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $returnUrl = $request->input('returnUrl');

        $user = AspNetUser::query()->where('NormalizedEmail', mb_strtoupper($credentials['email']))->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        // RULE 6/7: inactive accounts are blocked outright, without revealing
        // internal status details.
        if (! $user->IsActive) {
            return back()->withErrors(['email' => 'Your account is currently unavailable. Please contact the Sinaglahi Artists Group administration.'])->withInput();
        }

        $artist = Artist::query()->where('UserId', $user->Id)->first();
        if ($artist) {
            $message = match ((int) $artist->AccountStatus) {
                Artist::STATUS_PENDING => 'Your registration is currently under review.',
                Artist::STATUS_REJECTED => 'Your artist registration was not approved. Please contact the Sinaglahi Artists Group administration.',
                Artist::STATUS_INACTIVE => 'Your artist account is currently unavailable. Please contact the Sinaglahi Artists Group administration.',
                default => null,
            };
            if ($message) {
                return back()->withErrors(['email' => $message])->withInput();
            }
        }

        if (! Hash::check($credentials['password'], $user->getAuthPassword())) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['LastLoginAt' => now()])->save();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($artist) {
            return redirect()->route('artist.dashboard');
        }

        return $returnUrl ? redirect()->to($returnUrl) : redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
