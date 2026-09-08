<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Testimonials / Quotes management - mirrors Areas/Admin/Controllers/TestimonialsController. */
class TestimonialsController extends Controller
{
    private const SUBFOLDER = 'testimonials';

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(): View
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::query()->orderBy('DisplayOrder')->get()]);
    }

    public function create(): View
    {
        return view('admin.testimonials.form', ['testimonial' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $imagePath = null;
        if ($request->hasFile('Image')) {
            $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::SUBFOLDER);
            if ($upload->success) {
                $imagePath = $upload->storedPath;
            }
        }

        Testimonial::query()->create([
            'Name' => trim($data['Name']),
            'Position' => ! empty($data['Position']) ? trim($data['Position']) : null,
            'Quote' => trim($data['Quote']),
            'ImagePath' => $imagePath,
            'Date' => $data['Date'] ?? null,
            'IsPublished' => $request->boolean('IsPublished'),
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
        ]);

        return redirect()->route('admin.testimonials.index')->with('testimonialMessage', 'Testimonial added.');
    }

    public function edit(int $id): View
    {
        $testimonial = Testimonial::query()->find($id);
        if (! $testimonial) {
            abort(404);
        }

        return view('admin.testimonials.form', ['testimonial' => $testimonial]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->find($id);
        if (! $testimonial) {
            abort(404);
        }

        $data = $this->validated($request);

        if ($request->hasFile('Image')) {
            $upload = $this->fileStorage->savePublicImage($request->file('Image'), self::SUBFOLDER);
            if ($upload->success) {
                $this->fileStorage->deletePublicImage($testimonial->ImagePath);
                $testimonial->ImagePath = $upload->storedPath;
            }
        }

        $testimonial->Name = trim($data['Name']);
        $testimonial->Position = ! empty($data['Position']) ? trim($data['Position']) : null;
        $testimonial->Quote = trim($data['Quote']);
        $testimonial->Date = $data['Date'] ?? null;
        $testimonial->IsPublished = $request->boolean('IsPublished');
        $testimonial->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $testimonial->save();

        return redirect()->route('admin.testimonials.index')->with('testimonialMessage', 'Testimonial updated.');
    }

    public function togglePublished(int $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->find($id);
        if ($testimonial) {
            $testimonial->IsPublished = ! $testimonial->IsPublished;
            $testimonial->save();
        }

        return redirect()->route('admin.testimonials.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->find($id);
        if ($testimonial) {
            $this->fileStorage->deletePublicImage($testimonial->ImagePath);
            $testimonial->delete();
        }

        return redirect()->route('admin.testimonials.index')->with('testimonialMessage', 'Testimonial permanently deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Name' => ['required', 'max:150'],
            'Position' => ['nullable', 'max:150'],
            'Quote' => ['required', 'max:1000'],
            'Date' => ['nullable', 'date'],
            'DisplayOrder' => ['nullable', 'integer'],
            'Image' => ['nullable', 'image', 'max:10240'],
        ]);
    }
}
