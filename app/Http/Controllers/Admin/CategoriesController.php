<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Artwork categories - mirrors Areas/Admin/Controllers/CategoriesController in the .NET app. */
class CategoriesController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('artworks as ArtworkCount')
            ->orderBy('DisplayOrder')->orderBy('Name')
            ->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $category = Category::query()->find($id);
        if ($category) {
            $category->IsActive = ! $category->IsActive;
            $category->save();
        }

        return redirect()->route('admin.categories.index');
    }
}
