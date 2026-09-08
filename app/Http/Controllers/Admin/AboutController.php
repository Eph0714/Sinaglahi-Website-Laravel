<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\CoreValue;
use App\Services\FileStorageService;
use App\Services\HtmlSanitizerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** About Us / Mission / Vision / Core Values management - mirrors Areas/Admin/Controllers/AboutController in the .NET app. */
class AboutController extends Controller
{
    private const SUBFOLDER = 'about';

    public function __construct(
        private readonly FileStorageService $fileStorage,
        private readonly HtmlSanitizerService $sanitizer,
    ) {}

    public function index(): View
    {
        $about = $this->getOrCreate();

        return view('admin.about.index', [
            'about' => $about,
            'coreValues' => $about->coreValues()->orderBy('DisplayOrder')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $about = $this->getOrCreate();

        $data = $request->validate([
            'Title' => ['required', 'max:200'],
            'Introduction' => ['nullable', 'string'],
            'History' => ['nullable', 'string'],
            'Mission' => ['nullable', 'string'],
            'Vision' => ['nullable', 'string'],
            'Goals' => ['nullable', 'string'],
            'OrganizationStory' => ['nullable', 'string'],
            'LeadershipInfo' => ['nullable', 'string'],
            'FeaturedImage' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('FeaturedImage')) {
            $upload = $this->fileStorage->savePublicImage($request->file('FeaturedImage'), self::SUBFOLDER);
            if ($upload->success) {
                $this->fileStorage->deletePublicImage($about->FeaturedImagePath);
                $about->FeaturedImagePath = $upload->storedPath;
            }
        }

        $about->Title = trim($data['Title']);
        $about->Introduction = $this->sanitizer->sanitize($data['Introduction'] ?? '');
        $about->History = $this->sanitizer->sanitize($data['History'] ?? '');
        $about->Mission = $this->sanitizer->sanitize($data['Mission'] ?? '');
        $about->Vision = $this->sanitizer->sanitize($data['Vision'] ?? '');
        $about->Goals = $this->sanitizer->sanitize($data['Goals'] ?? '');
        $about->OrganizationStory = $this->sanitizer->sanitize($data['OrganizationStory'] ?? '');
        $about->LeadershipInfo = $this->sanitizer->sanitize($data['LeadershipInfo'] ?? '');
        $about->UpdatedAt = now();
        $about->UpdatedByUserId = auth()->id();
        $about->save();

        return redirect()->route('admin.about.index')->with('aboutMessage', 'About Us content saved.');
    }

    // ---------------- Core Values ----------------

    public function storeCoreValue(Request $request): RedirectResponse
    {
        $about = $this->getOrCreate();
        $data = $this->validatedCoreValue($request);

        CoreValue::query()->create([
            'AboutContentId' => $about->Id,
            'Title' => trim($data['Title']),
            'Description' => ! empty($data['Description']) ? trim($data['Description']) : null,
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'IsActive' => $request->boolean('IsActive'),
        ]);

        return redirect()->route('admin.about.index')->with('aboutMessage', 'Core value added.');
    }

    public function updateCoreValue(Request $request): RedirectResponse
    {
        $value = CoreValue::query()->find($request->integer('Id'));
        if (! $value) {
            abort(404);
        }

        $data = $this->validatedCoreValue($request);

        $value->Title = trim($data['Title']);
        $value->Description = ! empty($data['Description']) ? trim($data['Description']) : null;
        $value->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $value->IsActive = $request->boolean('IsActive');
        $value->save();

        return redirect()->route('admin.about.index')->with('aboutMessage', 'Core value updated.');
    }

    public function destroyCoreValue(int $id): RedirectResponse
    {
        $value = CoreValue::query()->find($id);
        if ($value) {
            $value->delete();
        }

        return redirect()->route('admin.about.index')->with('aboutMessage', 'Core value permanently deleted.');
    }

    private function getOrCreate(): AboutContent
    {
        return AboutContent::current() ?? AboutContent::query()->create([]);
    }

    private function validatedCoreValue(Request $request): array
    {
        return $request->validate([
            'Title' => ['required', 'max:100'],
            'Description' => ['nullable', 'max:500'],
            'DisplayOrder' => ['nullable', 'integer'],
        ]);
    }
}
