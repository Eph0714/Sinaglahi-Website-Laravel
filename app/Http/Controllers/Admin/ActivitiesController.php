<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\ActivityPhoto;
use App\Services\FileStorageService;
use App\Services\SlugHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Group Activities CRUD + photo gallery management - mirrors
 * Areas/Admin/Controllers/ActivitiesController in the .NET app.
 */
class ActivitiesController extends Controller
{
    private const PHOTO_SUBFOLDER = 'activities';

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(Request $request): View
    {
        $pageSize = 20;
        $page = max(1, (int) $request->integer('page', 1));

        $query = Activity::query()->with('category')->withCount('photos as PhotoCount');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")->orWhere('Location', 'like', "%{$search}%");
            });
        }

        $filter = $request->query('filter');
        match ($filter) {
            'Published' => $query->where('IsPublished', true),
            'Draft' => $query->where('IsPublished', false),
            'Featured' => $query->where('IsFeatured', true),
            default => null,
        };

        $total = (clone $query)->count();
        $activities = $query->orderByDesc('ActivityDate')->forPage($page, $pageSize)->get();

        return view('admin.activities.index', [
            'activities' => $activities,
            'search' => $search ?: null,
            'filter' => $filter,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    public function create(): View
    {
        return view('admin.activities.form', [
            'activity' => null,
            'categoryOptions' => $this->categoryOptions(),
            'formAction' => route('admin.activities.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $coverPath = null;
        if ($request->hasFile('CoverPhoto')) {
            $upload = $this->fileStorage->savePublicImage($request->file('CoverPhoto'), self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['CoverPhoto' => $upload->error])->withInput();
            }
            $coverPath = $upload->storedPath;
        }

        $activity = Activity::query()->create([
            'Title' => trim($data['Title']),
            'Slug' => $this->uniqueSlug($data['Title']),
            'CategoryId' => $data['CategoryId'],
            'ActivityDate' => $data['ActivityDate'],
            'Location' => trim($data['Location']),
            'Description' => trim($data['Description']),
            'CoverPhotoPath' => $coverPath,
            'FacebookUrl' => ! empty($data['FacebookUrl']) ? trim($data['FacebookUrl']) : null,
            'IsFeatured' => $request->boolean('IsFeatured'),
            'FeaturedOrder' => $data['FeaturedOrder'] ?? 0,
            'Status' => $data['Status'] ?? Activity::STATUS_UPCOMING,
            'IsPublished' => $request->boolean('IsPublished'),
            'CreatedDate' => now(),
        ]);

        return redirect()->route('admin.activities.manage-photos', $activity->Id)->with('activityMessage', 'Activity successfully created.');
    }

    public function edit(int $id): View
    {
        $activity = Activity::query()->find($id);
        if (! $activity) {
            abort(404);
        }

        return view('admin.activities.form', [
            'activity' => $activity,
            'categoryOptions' => $this->categoryOptions(),
            'formAction' => route('admin.activities.update', $activity->Id),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $activity = Activity::query()->find($id);
        if (! $activity) {
            abort(404);
        }

        $data = $this->validated($request);

        if ($request->hasFile('CoverPhoto')) {
            $upload = $this->fileStorage->savePublicImage($request->file('CoverPhoto'), self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                return back()->withErrors(['CoverPhoto' => $upload->error])->withInput();
            }
            $this->fileStorage->deletePublicImage($activity->CoverPhotoPath);
            $activity->CoverPhotoPath = $upload->storedPath;
        }

        $activity->Title = trim($data['Title']);
        $activity->CategoryId = $data['CategoryId'];
        $activity->ActivityDate = $data['ActivityDate'];
        $activity->Location = trim($data['Location']);
        $activity->Description = trim($data['Description']);
        $activity->FacebookUrl = ! empty($data['FacebookUrl']) ? trim($data['FacebookUrl']) : null;
        $activity->IsFeatured = $request->boolean('IsFeatured');
        $activity->FeaturedOrder = $data['FeaturedOrder'] ?? 0;
        $activity->Status = $data['Status'] ?? Activity::STATUS_UPCOMING;
        $activity->IsPublished = $request->boolean('IsPublished');
        $activity->UpdatedDate = now();
        $activity->save();

        return redirect()->route('admin.activities.index')
            ->with('activityMessage', $activity->IsPublished ? 'Activity successfully published.' : 'Activity successfully updated.');
    }

    public function publish(int $id): RedirectResponse
    {
        $activity = Activity::query()->find($id);
        if ($activity && ! $activity->IsPublished) {
            $activity->IsPublished = true;
            $activity->save();
        }

        return redirect()->route('admin.activities.index')->with('activityMessage', 'Activity successfully published.');
    }

    public function unpublish(int $id): RedirectResponse
    {
        $activity = Activity::query()->find($id);
        if ($activity && $activity->IsPublished) {
            $activity->IsPublished = false;
            $activity->save();
        }

        return redirect()->route('admin.activities.index')->with('activityMessage', 'Activity successfully unpublished.');
    }

    public function toggleFeatured(int $id): RedirectResponse
    {
        $activity = Activity::query()->find($id);
        if ($activity) {
            $activity->IsFeatured = ! $activity->IsFeatured;
            $activity->save();
        }

        return redirect()->route('admin.activities.index')
            ->with('activityMessage', $activity?->IsFeatured ? 'Activity added to featured.' : 'Activity removed from featured.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $activity = Activity::query()->with('photos')->find($id);
        if ($activity) {
            $this->fileStorage->deletePublicImage($activity->CoverPhotoPath);
            foreach ($activity->photos as $photo) {
                $this->fileStorage->deletePublicImage($photo->FilePath, $photo->ThumbnailPath);
            }
            $activity->delete(); // cascades to ActivityPhotos rows
        }

        return redirect()->route('admin.activities.index')->with('activityMessage', 'Activity successfully deleted.');
    }

    // ---------------- Photo management ----------------

    public function managePhotos(int $id): View
    {
        $activity = Activity::query()->with('photos')->find($id);
        if (! $activity) {
            abort(404);
        }

        return view('admin.activities.manage-photos', [
            'activity' => $activity,
            'photos' => $activity->photos->sortBy('DisplayOrder')->values(),
        ]);
    }

    public function uploadPhotos(Request $request): JsonResponse|RedirectResponse
    {
        $id = (int) $request->input('id');
        $activity = Activity::query()->with('photos')->find($id);
        if (! $activity) {
            abort(404);
        }

        $nextOrder = $activity->photos->isEmpty() ? 0 : $activity->photos->max('DisplayOrder') + 1;
        $successCount = 0;
        $errors = [];

        foreach ($request->file('files', []) as $file) {
            $upload = $this->fileStorage->savePublicImageWithThumbnail($file, self::PHOTO_SUBFOLDER);
            if (! $upload->success) {
                $errors[] = "{$file->getClientOriginalName()}: {$upload->error}";

                continue;
            }

            $isCover = $activity->CoverPhotoPath === null && $successCount === 0;
            $photo = ActivityPhoto::query()->create([
                'ActivityId' => $activity->Id,
                'FilePath' => $upload->storedPath,
                'ThumbnailPath' => $upload->thumbnailPath,
                'DisplayOrder' => $nextOrder++,
                'IsCover' => $isCover,
                'UploadedByUserId' => auth()->id(),
                'UploadedDate' => now(),
            ]);

            if ($isCover) {
                $activity->CoverPhotoPath = $photo->FilePath;
                $activity->save();
            }

            $successCount++;
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'uploaded' => $successCount, 'errors' => $errors]);
        }

        return redirect()->route('admin.activities.manage-photos', $id)
            ->with('activityMessage', $errors === [] ? 'Photos successfully uploaded.' : "{$successCount} photo(s) uploaded; ".count($errors).' failed.');
    }

    public function setCoverPhoto(Request $request): RedirectResponse
    {
        $id = (int) $request->input('id');
        $photoId = (int) $request->input('photoId');
        $activity = Activity::query()->with('photos')->find($id);
        if (! $activity) {
            abort(404);
        }

        foreach ($activity->photos as $p) {
            $shouldBeCover = $p->Id === $photoId;
            if ($p->IsCover !== $shouldBeCover) {
                $p->IsCover = $shouldBeCover;
                $p->UpdatedDate = now();
                $p->save();
            }
        }
        $chosen = $activity->photos->firstWhere('Id', $photoId);
        if ($chosen) {
            $activity->CoverPhotoPath = $chosen->FilePath;
            $activity->save();
        }

        return redirect()->route('admin.activities.manage-photos', $id);
    }

    public function updateCaption(Request $request): JsonResponse|RedirectResponse
    {
        $id = (int) $request->input('id');
        $photoId = (int) $request->input('photoId');
        $photo = ActivityPhoto::query()->where('Id', $photoId)->where('ActivityId', $id)->first();
        if ($photo) {
            $caption = $request->input('caption');
            $description = $request->input('description');
            $photo->Caption = trim((string) $caption) !== '' ? trim($caption) : null;
            $photo->Description = trim((string) $description) !== '' ? trim($description) : null;
            $photo->UpdatedDate = now();
            $photo->save();
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.activities.manage-photos', $id);
    }

    public function replacePhoto(Request $request): JsonResponse|RedirectResponse
    {
        $id = (int) $request->input('id');
        $photoId = (int) $request->input('photoId');
        $photo = ActivityPhoto::query()->where('Id', $photoId)->where('ActivityId', $id)->first();
        if (! $photo) {
            abort(404);
        }

        $upload = $this->fileStorage->savePublicImageWithThumbnail($request->file('file'), self::PHOTO_SUBFOLDER);
        if (! $upload->success) {
            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'error' => $upload->error]);
            }

            return redirect()->route('admin.activities.manage-photos', $id)->with('activityMessage', $upload->error);
        }

        $old = $photo->FilePath;
        $oldThumb = $photo->ThumbnailPath;
        $photo->FilePath = $upload->storedPath;
        $photo->ThumbnailPath = $upload->thumbnailPath;
        $photo->UpdatedDate = now();
        $photo->save();

        $activity = Activity::query()->find($id);
        if ($activity && $photo->IsCover) {
            $activity->CoverPhotoPath = $photo->FilePath;
            $activity->save();
        }

        $this->fileStorage->deletePublicImage($old, $oldThumb);

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'filePath' => $photo->FilePath, 'thumbnailPath' => $photo->ThumbnailPath]);
        }

        return redirect()->route('admin.activities.manage-photos', $id)->with('activityMessage', 'Photo replaced successfully.');
    }

    public function reorderPhotos(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $orderedIds = (array) $request->input('orderedPhotoIds', []);
        $photos = ActivityPhoto::query()->where('ActivityId', $id)->get()->keyBy('Id');

        foreach ($orderedIds as $i => $photoId) {
            $photo = $photos->get((int) $photoId);
            if ($photo && (int) $photo->DisplayOrder !== $i) {
                $photo->DisplayOrder = $i;
                $photo->UpdatedDate = now();
                $photo->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function hidePhoto(Request $request, int $id, int $photoId): JsonResponse|RedirectResponse
    {
        $photo = ActivityPhoto::query()->where('Id', $photoId)->where('ActivityId', $id)->first();
        if (! $photo) {
            abort(404);
        }
        if (! $photo->IsHidden) {
            $photo->IsHidden = true;
            $photo->UpdatedDate = now();
            if ($photo->IsCover) {
                $this->reassignCoverPhoto($id, excludePhotoId: $photo->Id);
            }
            $photo->save();
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.activities.manage-photos', $id);
    }

    public function unhidePhoto(Request $request, int $id, int $photoId): JsonResponse|RedirectResponse
    {
        $photo = ActivityPhoto::query()->where('Id', $photoId)->where('ActivityId', $id)->first();
        if (! $photo) {
            abort(404);
        }
        if ($photo->IsHidden) {
            $photo->IsHidden = false;
            $photo->UpdatedDate = now();
            $photo->save();
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.activities.manage-photos', $id);
    }

    public function deletePhoto(Request $request): JsonResponse|RedirectResponse
    {
        $id = (int) $request->input('id');
        $photoId = (int) $request->input('photoId');
        $photo = ActivityPhoto::query()->where('Id', $photoId)->where('ActivityId', $id)->first();
        if ($photo) {
            $wasCover = $photo->IsCover;
            $this->fileStorage->deletePublicImage($photo->FilePath, $photo->ThumbnailPath);
            $photo->delete();

            if ($wasCover) {
                $this->reassignCoverPhoto($id, excludePhotoId: $photoId);
            }
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.activities.manage-photos', $id)->with('activityMessage', 'Photo deleted successfully.');
    }

    public function bulkDeletePhotos(Request $request): RedirectResponse
    {
        $id = (int) $request->input('id');
        $photoIds = (array) $request->input('photoIds', []);
        $photos = ActivityPhoto::query()->where('ActivityId', $id)->whereIn('Id', $photoIds)->get();
        $coverWasDeleted = $photos->contains('IsCover', true);

        foreach ($photos as $photo) {
            $this->fileStorage->deletePublicImage($photo->FilePath, $photo->ThumbnailPath);
            $photo->delete();
        }

        if ($coverWasDeleted) {
            $this->reassignCoverPhoto($id, excludePhotoId: 0);
        }

        return redirect()->route('admin.activities.manage-photos', $id)
            ->with('activityMessage', $photos->count().' photo(s) deleted successfully.');
    }

    /**
     * Picks a replacement Featured/Cover photo when the current one is deleted or
     * deactivated: the next active photo in display order becomes cover, or the
     * activity is left with no cover if none remain.
     */
    private function reassignCoverPhoto(int $activityId, int $excludePhotoId): void
    {
        $activity = Activity::query()->with('photos')->find($activityId);
        if (! $activity) {
            return;
        }

        $outgoing = $activity->photos->firstWhere('Id', $excludePhotoId);
        if ($outgoing) {
            $outgoing->IsCover = false;
            $outgoing->save();
        }

        $replacement = $activity->photos
            ->where('Id', '!=', $excludePhotoId)
            ->where('IsHidden', false)
            ->sortBy('DisplayOrder')
            ->first();

        if ($replacement) {
            $replacement->IsCover = true;
            $replacement->UpdatedDate = now();
            $replacement->save();
            $activity->CoverPhotoPath = $replacement->FilePath;
        } else {
            $activity->CoverPhotoPath = null;
        }
        $activity->save();
    }

    private function categoryOptions(): Collection
    {
        return ActivityCategory::query()->active()->orderBy('DisplayOrder')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Title' => ['required', 'max:200'],
            'CategoryId' => ['required', 'integer'],
            'ActivityDate' => ['required', 'date'],
            'Location' => ['required', 'max:200'],
            'Description' => ['required', 'max:4000'],
            'FacebookUrl' => ['nullable', 'url', 'max:500'],
            'FeaturedOrder' => ['nullable', 'integer'],
            'Status' => ['nullable', 'integer', 'between:0,2'],
            'CoverPhoto' => ['nullable', 'image', 'max:10240'],
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = SlugHelper::generateSlug($title) ?: 'activity';
        $slug = $base;
        $suffix = 1;
        while (Activity::query()->where('Slug', $slug)->exists()) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
