<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\FileStorageService;
use App\Services\HtmlSanitizerService;
use App\Services\SlugHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Custom Pages management - mirrors Areas/Admin/Controllers/PagesController. */
class PagesController extends Controller
{
    private const SUBFOLDER = 'pages';

    public function __construct(
        private readonly FileStorageService $fileStorage,
        private readonly HtmlSanitizerService $sanitizer,
    ) {}

    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::query()->orderBy('DisplayOrder')->get()]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $slugSource = ! empty($data['Slug']) ? $data['Slug'] : $data['Title'];
        $slug = $this->uniqueSlug($slugSource, null);

        $featuredImagePath = null;
        if ($request->hasFile('FeaturedImage')) {
            $upload = $this->fileStorage->savePublicImage($request->file('FeaturedImage'), self::SUBFOLDER);
            if ($upload->success) {
                $featuredImagePath = $upload->storedPath;
            }
        }

        Page::query()->create([
            'Title' => trim($data['Title']),
            'Slug' => $slug,
            'Content' => $this->sanitizer->sanitize($data['Content'] ?? ''),
            'FeaturedImagePath' => $featuredImagePath,
            'SeoTitle' => ! empty($data['SeoTitle']) ? trim($data['SeoTitle']) : null,
            'SeoDescription' => ! empty($data['SeoDescription']) ? trim($data['SeoDescription']) : null,
            'IsPublished' => $request->boolean('IsPublished'),
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('admin.pages.index')->with('pageMessage', 'Page successfully created.');
    }

    public function edit(int $id): View
    {
        $page = Page::query()->find($id);
        if (! $page) {
            abort(404);
        }

        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $page = Page::query()->find($id);
        if (! $page) {
            abort(404);
        }

        $data = $this->validated($request);

        if ($request->hasFile('FeaturedImage')) {
            $upload = $this->fileStorage->savePublicImage($request->file('FeaturedImage'), self::SUBFOLDER);
            if ($upload->success) {
                $this->fileStorage->deletePublicImage($page->FeaturedImagePath);
                $page->FeaturedImagePath = $upload->storedPath;
            }
        }

        $newSlugSource = ! empty($data['Slug']) ? $data['Slug'] : $data['Title'];
        if (mb_strtolower($newSlugSource) !== mb_strtolower($page->Slug)) {
            $page->Slug = $this->uniqueSlug($newSlugSource, $page->Id);
        }

        $page->Title = trim($data['Title']);
        $page->Content = $this->sanitizer->sanitize($data['Content'] ?? '');
        $page->SeoTitle = ! empty($data['SeoTitle']) ? trim($data['SeoTitle']) : null;
        $page->SeoDescription = ! empty($data['SeoDescription']) ? trim($data['SeoDescription']) : null;
        $page->IsPublished = $request->boolean('IsPublished');
        $page->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $page->UpdatedAt = now();
        $page->save();

        return redirect()->route('admin.pages.index')->with('pageMessage', 'Page successfully updated.');
    }

    public function togglePublished(int $id): RedirectResponse
    {
        $page = Page::query()->find($id);
        if ($page) {
            $page->IsPublished = ! $page->IsPublished;
            $page->save();
        }

        return redirect()->route('admin.pages.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $page = Page::query()->find($id);
        if ($page) {
            $this->fileStorage->deletePublicImage($page->FeaturedImagePath);
            $page->delete();
        }

        return redirect()->route('admin.pages.index')->with('pageMessage', 'Page permanently deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Title' => ['required', 'max:200'],
            'Slug' => ['nullable', 'max:220'],
            'Content' => ['nullable', 'string'],
            'SeoTitle' => ['nullable', 'max:200'],
            'SeoDescription' => ['nullable', 'max:300'],
            'DisplayOrder' => ['nullable', 'integer'],
            'FeaturedImage' => ['nullable', 'image', 'max:10240'],
        ]);
    }

    private function uniqueSlug(string $source, ?int $excludeId): string
    {
        $base = SlugHelper::generateSlug($source) ?: 'page';
        $slug = $base;
        $suffix = 1;
        while (Page::query()->where('Slug', $slug)->when($excludeId, fn ($q) => $q->where('Id', '!=', $excludeId))->exists()) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
