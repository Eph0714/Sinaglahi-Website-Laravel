<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

/** Public FAQ listing - mirrors Controllers/FaqController in the .NET app. */
class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()->published()->orderBy('DisplayOrder')->get();

        return view('faq.index', ['faqs' => $faqs]);
    }
}
