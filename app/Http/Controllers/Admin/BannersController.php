<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteBanner;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Homepage Banner / Hero management - mirrors Areas/Admin/Controllers/BannersController. */
class BannersController extends Controller
{
    private const SUBFOLDER = 'banners';

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(): View
    {
        return view('admin.banners.index', ['banners' => SiteBanner::query()->orderBy('DisplayOrder')->get()]);
    }

    public function create(): View
    {
        return view('admin.banners.form', ['banner' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if (! $request->hasFile('Image')) {
            return back()->withErrors(['Image' => 'Please choose a banner image.'])->withInput();
        }
        $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::SUBFOLDER);
        if (! $upload->success) {
            return back()->withErrors(['Image' => $upload->error])->withInput();
        }

        SiteBanner::query()->create([
            'Title' => trim($data['Title']),
            'Subtitle' => ! empty($data['Subtitle']) ? trim($data['Subtitle']) : null,
            'Description' => ! empty($data['Description']) ? trim($data['Description']) : null,
            'ImagePath' => $upload->storedPath,
            'ButtonText' => ! empty($data['ButtonText']) ? trim($data['ButtonText']) : null,
            'ButtonUrl' => ! empty($data['ButtonUrl']) ? trim($data['ButtonUrl']) : null,
            'SecondButtonText' => ! empty($data['SecondButtonText']) ? trim($data['SecondButtonText']) : null,
            'SecondButtonUrl' => ! empty($data['SecondButtonUrl']) ? trim($data['SecondButtonUrl']) : null,
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'IsEnabled' => $request->boolean('IsEnabled'),
            'IsFeatured' => $request->boolean('IsFeatured'),
            'CreatedAt' => now(),
        ]);

        return redirect()->route('admin.banners.index')->with('bannerMessage', 'Banner successfully created.');
    }

    public function edit(int $id): View
    {
        $banner = SiteBanner::query()->find($id);
        if (! $banner) {
            abort(404);
        }

        return view('admin.banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $banner = SiteBanner::query()->find($id);
        if (! $banner) {
            abort(404);
        }

        $data = $this->validated($request);

        if ($request->hasFile('Image')) {
            $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['Image' => $upload->error])->withInput();
            }
            $this->fileStorage->deletePublicImage($banner->ImagePath);
            $banner->ImagePath = $upload->storedPath;
        }

        $banner->Title = trim($data['Title']);
        $banner->Subtitle = ! empty($data['Subtitle']) ? trim($data['Subtitle']) : null;
        $banner->Description = ! empty($data['Description']) ? trim($data['Description']) : null;
        $banner->ButtonText = ! empty($data['ButtonText']) ? trim($data['ButtonText']) : null;
        $banner->ButtonUrl = ! empty($data['ButtonUrl']) ? trim($data['ButtonUrl']) : null;
        $banner->SecondButtonText = ! empty($data['SecondButtonText']) ? trim($data['SecondButtonText']) : null;
        $banner->SecondButtonUrl = ! empty($data['SecondButtonUrl']) ? trim($data['SecondButtonUrl']) : null;
        $banner->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $banner->IsEnabled = $request->boolean('IsEnabled');
        $banner->IsFeatured = $request->boolean('IsFeatured');
        $banner->UpdatedAt = now();
        $banner->save();

        return redirect()->route('admin.banners.index')->with('bannerMessage', 'Banner successfully updated.');
    }

    public function toggleEnabled(int $id): RedirectResponse
    {
        $banner = SiteBanner::query()->find($id);
        if ($banner) {
            $banner->IsEnabled = ! $banner->IsEnabled;
            $banner->save();
        }

        return redirect()->route('admin.banners.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $banner = SiteBanner::query()->find($id);
        if ($banner) {
            $this->fileStorage->deletePublicImage($banner->ImagePath);
            $banner->delete();
        }

        return redirect()->route('admin.banners.index')->with('bannerMessage', 'Banner permanently deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Title' => ['required', 'max:200'],
            'Subtitle' => ['nullable', 'max:200'],
            'Description' => ['nullable', 'max:1000'],
            'ButtonText' => ['nullable', 'max:100'],
            'ButtonUrl' => ['nullable', 'max:500'],
            'SecondButtonText' => ['nullable', 'max:100'],
            'SecondButtonUrl' => ['nullable', 'max:500'],
            'DisplayOrder' => ['nullable', 'integer'],
            'Image' => ['nullable', 'image', 'max:10240'],
        ]);
    }
}
