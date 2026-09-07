<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupActivitiesController extends Controller
{
    /** Mirrors GroupActivitiesService.SearchAsync in the .NET app. */
    public function index(Request $request): View
    {
        $pageSize = 12;
        $page = max(1, (int) $request->integer('page', 1));

        $query = Activity::query()->published()->with('category')->withCount(['visiblePhotos as PhotoCount']);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                    ->orWhere('Location', 'like', "%{$search}%")
                    ->orWhere('Description', 'like', "%{$search}%");
            });
        }
        if ($categoryId = $request->query('categoryId')) {
            $query->where('CategoryId', $categoryId);
        }
        if ($year = $request->query('year')) {
            $query->whereYear('ActivityDate', $year);
        }

        $featured = Activity::query()->published()->featured()
            ->withCount(['visiblePhotos as PhotoCount'])
            ->orderBy('FeaturedOrder')
            ->take(3)
            ->get();

        $total = (clone $query)->count();
        $activities = $query->orderByDesc('ActivityDate')
            ->forPage($page, $pageSize)
            ->get();

        $categories = ActivityCategory::query()->active()->orderBy('DisplayOrder')->get();
        $years = Activity::query()->published()->selectRaw('DISTINCT YEAR(ActivityDate) as y')
            ->orderByDesc('y')->pluck('y');

        return view('group-activities.index', [
            'activities' => $activities,
            'featured' => $featured,
            'categories' => $categories,
            'years' => $years,
            'search' => $search ?? null,
            'categoryId' => $categoryId,
            'year' => $year,
            'page' => $page,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }

    /** Mirrors GroupActivitiesService.GetDetailAsync in the .NET app. */
    public function show(string $slug): View
    {
        $activity = Activity::query()->published()
            ->with(['category', 'visiblePhotos'])
            ->where('Slug', $slug)
            ->first();

        if (! $activity) {
            throw new NotFoundHttpException;
        }

        return view('group-activities.show', ['activity' => $activity]);
    }
}
