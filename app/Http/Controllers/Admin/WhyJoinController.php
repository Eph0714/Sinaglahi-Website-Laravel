<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhyJoinBenefit;
use App\Models\WhyJoinPhoto;
use App\Models\WhyJoinSection;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages the public homepage's "Why Join Sinaglahi Artists?" section - its
 * editable copy, benefit cards, and photo gallery. Mirrors Areas/Admin/
 * Controllers/WhyJoinController in the .NET app.
 */
class WhyJoinController extends Controller
{
    private const PHOTO_SUBFOLDER = 'why-join';

    public function __construct(private readonly FileStorageService $fileStorage) {}

    // ---------------- Section content ----------------

    public function index(): View
    {
        return view('admin.why-join.index', ['section' => WhyJoinSection::query()->firstOrCreate([])]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Title' => ['required', 'max:200'],
            'Subtitle' => ['required', 'max:200'],
            'Introduction' => ['required', 'max:2000'],
            'FeaturedStatement' => ['required', 'max:300'],
            'SupportingParagraph' => ['required', 'max:1000'],
            'ButtonText' => ['required', 'max:100'],
            'ButtonUrl' => ['required', 'max:500'],
        ]);

        $s = WhyJoinSection::query()->firstOrCreate([]);
        foreach ($data as $key => $value) {
            $s->{$key} = trim($value);
        }
        $s->UpdatedAt = now();
        $s->UpdatedByUserId = auth()->id();
        $s->save();

        return redirect()->route('admin.why-join.index')->with('whyJoinMessage', 'Section content saved.');
    }

    // ---------------- Benefit cards ----------------

    public function benefits(): View
    {
        return view('admin.why-join.benefits', ['benefits' => WhyJoinBenefit::query()->orderBy('DisplayOrder')->get()]);
    }

    public function createBenefit(): View
    {
        return view('admin.why-join.benefit-form', ['benefit' => null]);
    }

    public function storeBenefit(Request $request): RedirectResponse
    {
        $data = $this->validatedBenefit($request);

        $maxOrder = WhyJoinBenefit::query()->max('DisplayOrder') ?? 0;
        WhyJoinBenefit::query()->create([
            'Icon' => trim($data['Icon']),
            'Title' => trim($data['Title']),
            'Description' => trim($data['Description']),
            'DisplayOrder' => $maxOrder + 1,
            'IsActive' => $request->boolean('IsActive'),
            'CreatedAt' => now(),
        ]);

        return redirect()->route('admin.why-join.benefits')->with('whyJoinMessage', 'Benefit card added.');
    }

    public function editBenefit(int $id): View
    {
        $benefit = WhyJoinBenefit::query()->find($id);
        if (! $benefit) {
            abort(404);
        }

        return view('admin.why-join.benefit-form', ['benefit' => $benefit]);
    }

    public function updateBenefit(Request $request, int $id): RedirectResponse
    {
        $benefit = WhyJoinBenefit::query()->find($id);
        if (! $benefit) {
            abort(404);
        }

        $data = $this->validatedBenefit($request);
        $benefit->Icon = trim($data['Icon']);
        $benefit->Title = trim($data['Title']);
        $benefit->Description = trim($data['Description']);
        $benefit->IsActive = $request->boolean('IsActive');
        $benefit->UpdatedAt = now();
        $benefit->save();

        return redirect()->route('admin.why-join.benefits')->with('whyJoinMessage', 'Benefit card updated.');
    }

    public function destroyBenefit(int $id): RedirectResponse
    {
        WhyJoinBenefit::query()->where('Id', $id)->delete();

        return redirect()->route('admin.why-join.benefits')->with('whyJoinMessage', 'Benefit card deleted.');
    }

    public function moveBenefit(Request $request, int $id): RedirectResponse
    {
        $direction = $request->input('direction');
        $benefits = WhyJoinBenefit::query()->orderBy('DisplayOrder')->get();
        $index = $benefits->search(fn (WhyJoinBenefit $b) => $b->Id === $id);
        if ($index === false) {
            return redirect()->route('admin.why-join.benefits');
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= $benefits->count()) {
            return redirect()->route('admin.why-join.benefits');
        }

        $a = $benefits->get($index);
        $b = $benefits->get($swapWith);
        [$a->DisplayOrder, $b->DisplayOrder] = [$b->DisplayOrder, $a->DisplayOrder];
        $a->save();
        $b->save();

        return redirect()->route('admin.why-join.benefits');
    }

    // ---------------- Photo gallery ----------------

    public function photos(): View
    {
        return view('admin.why-join.photos', ['photos' => WhyJoinPhoto::query()->orderBy('DisplayOrder')->get()]);
    }

    public function createPhoto(): View
    {
        return view('admin.why-join.photo-form', ['photo' => null]);
    }

    public function storePhoto(Request $request): RedirectResponse
    {
        $data = $this->validatedPhoto($request);

        if (! $request->hasFile('Image')) {
            return back()->withErrors(['Image' => 'Please choose a photo.'])->withInput();
        }
        $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::PHOTO_SUBFOLDER);
        if (! $upload->success) {
            return back()->withErrors(['Image' => $upload->error])->withInput();
        }

        $maxOrder = WhyJoinPhoto::query()->max('DisplayOrder') ?? 0;
        $isFirstPhoto = ! WhyJoinPhoto::query()->exists();
        $isFeatured = $isFirstPhoto || $request->boolean('IsFeatured');

        if ($isFeatured) {
            WhyJoinPhoto::query()->where('IsFeatured', true)->update(['IsFeatured' => false]);
        }

        WhyJoinPhoto::query()->create([
            'ImagePath' => $upload->storedPath,
            'Title' => ! empty($data['Title']) ? trim($data['Title']) : null,
            'Caption' => ! empty($data['Caption']) ? trim($data['Caption']) : null,
            'Description' => ! empty($data['Description']) ? trim($data['Description']) : null,
            'DisplayOrder' => $maxOrder + 1,
            'IsActive' => $request->boolean('IsActive'),
            'IsFeatured' => $isFeatured,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('admin.why-join.photos')->with('whyJoinMessage', 'Photo added.');
    }

    public function editPhoto(int $id): View
    {
        $photo = WhyJoinPhoto::query()->find($id);
        if (! $photo) {
            abort(404);
        }

        return view('admin.why-join.photo-form', ['photo' => $photo]);
    }

    public function updatePhoto(Request $request, int $id): RedirectResponse
    {
        $photo = WhyJoinPhoto::query()->find($id);
        if (! $photo) {
            abort(404);
        }

        $data = $this->validatedPhoto($request);

        if ($request->hasFile('Image')) {
            $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['Image' => $upload->error])->withInput();
            }
            $this->fileStorage->deletePublicImage($photo->ImagePath);
            $photo->ImagePath = $upload->storedPath;
        }

        $photo->Title = ! empty($data['Title']) ? trim($data['Title']) : null;
        $photo->Caption = ! empty($data['Caption']) ? trim($data['Caption']) : null;
        $photo->Description = ! empty($data['Description']) ? trim($data['Description']) : null;
        $photo->IsActive = $request->boolean('IsActive');
        $photo->UpdatedAt = now();

        $wantsFeatured = $request->boolean('IsFeatured');
        if ($wantsFeatured && ! $photo->IsFeatured) {
            WhyJoinPhoto::query()->where('Id', '!=', $photo->Id)->where('IsFeatured', true)->update(['IsFeatured' => false]);
        }
        $photo->IsFeatured = $wantsFeatured;
        $photo->save();

        return redirect()->route('admin.why-join.photos')->with('whyJoinMessage', 'Photo updated.');
    }

    public function setFeaturedPhoto(int $id): RedirectResponse
    {
        $photo = WhyJoinPhoto::query()->find($id);
        if ($photo && ! $photo->IsFeatured) {
            WhyJoinPhoto::query()->where('IsFeatured', true)->update(['IsFeatured' => false]);
            $photo->IsFeatured = true;
            $photo->UpdatedAt = now();
            $photo->save();
        }

        return redirect()->route('admin.why-join.photos')->with('whyJoinMessage', 'Featured photo updated.');
    }

    public function toggleActivePhoto(int $id): RedirectResponse
    {
        $photo = WhyJoinPhoto::query()->find($id);
        if ($photo) {
            $photo->IsActive = ! $photo->IsActive;
            $photo->UpdatedAt = now();
            $photo->save();
        }

        return redirect()->route('admin.why-join.photos');
    }

    public function movePhoto(Request $request, int $id): RedirectResponse
    {
        $direction = $request->input('direction');
        $photos = WhyJoinPhoto::query()->orderBy('DisplayOrder')->get();
        $index = $photos->search(fn (WhyJoinPhoto $p) => $p->Id === $id);
        if ($index === false) {
            return redirect()->route('admin.why-join.photos');
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= $photos->count()) {
            return redirect()->route('admin.why-join.photos');
        }

        $a = $photos->get($index);
        $b = $photos->get($swapWith);
        [$a->DisplayOrder, $b->DisplayOrder] = [$b->DisplayOrder, $a->DisplayOrder];
        $a->save();
        $b->save();

        return redirect()->route('admin.why-join.photos');
    }

    /**
     * Force delete: permanently removes the photo and its file. If it was
     * the featured photo, the next active one in display order automatically
     * becomes featured so the public section is never left without one.
     */
    public function destroyPhoto(int $id): RedirectResponse
    {
        $photo = WhyJoinPhoto::query()->find($id);
        if ($photo) {
            $wasFeatured = $photo->IsFeatured;
            $this->fileStorage->deletePublicImage($photo->ImagePath);
            $photo->delete();

            if ($wasFeatured) {
                $next = WhyJoinPhoto::query()->where('IsActive', true)->orderBy('DisplayOrder')->first();
                if ($next) {
                    $next->IsFeatured = true;
                    $next->save();
                }
            }
        }

        return redirect()->route('admin.why-join.photos')->with('whyJoinMessage', 'Photo permanently deleted.');
    }

    private function validatedBenefit(Request $request): array
    {
        return $request->validate([
            'Icon' => ['required', 'max:20'],
            'Title' => ['required', 'max:150'],
            'Description' => ['required', 'max:500'],
        ]);
    }

    private function validatedPhoto(Request $request): array
    {
        return $request->validate([
            'Title' => ['nullable', 'max:200'],
            'Caption' => ['nullable', 'max:200'],
            'Description' => ['nullable', 'max:1000'],
            'Image' => ['nullable', 'image', 'max:10240'],
        ]);
    }
}
