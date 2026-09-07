<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PlaceholderController extends Controller
{
    /** Phase 2 (auth, Join wizard) pages - not ported yet. */
    public function comingSoon(): View
    {
        return view('pages.coming-soon');
    }
}
