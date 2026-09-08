<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Serves Super-Admin-authored custom pages at /pages/{slug}. */
class PagesController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()->published()->where('Slug', $slug)->first();
        if (! $page) {
            throw new NotFoundHttpException;
        }

        return view('pages.custom', ['page' => $page]);
    }
}
