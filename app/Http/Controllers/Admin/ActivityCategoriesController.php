<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Group Activities categories - mirrors Areas/Admin/Controllers/ActivityCategoriesController in the .NET app. */
class ActivityCategoriesController extends Controller
{
    public function index(): View
    {
        $categories = ActivityCategory::query()
            ->withCount('activities as ActivityCount')
            ->orderBy('DisplayOrder')
            ->get();

        return view('admin.activity-categories.index', ['categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if (ActivityCategory::query()->where('Name', trim($data['Name']))->exists()) {
            return redirect()->route('admin.activity-categories.index')
                ->with('categoryError', 'A category with this name already exists.');
        }

        ActivityCategory::query()->create([
            'Name' => trim($data['Name']),
            'Description' => ! empty($data['Description']) ? trim($data['Description']) : null,
            'IsActive' => $request->boolean('IsActive'),
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'CreatedDate' => now(),
        ]);

        return redirect()->route('admin.activity-categories.index')->with('categoryMessage', 'Category successfully created.');
    }

    public function update(Request $request): RedirectResponse
    {
        $id = $request->integer('Id');
        $category = ActivityCategory::query()->find($id);
        if (! $category) {
            abort(404);
        }

        $data = $this->validated($request);

        $category->Name = trim($data['Name']);
        $category->Description = ! empty($data['Description']) ? trim($data['Description']) : null;
        $category->IsActive = $request->boolean('IsActive');
        $category->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $category->UpdatedDate = now();
        $category->save();

        return redirect()->route('admin.activity-categories.index')->with('categoryMessage', 'Category successfully updated.');
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $category = ActivityCategory::query()->find($id);
        if ($category) {
            $category->IsActive = ! $category->IsActive;
            $category->UpdatedDate = now();
            $category->save();
        }

        return redirect()->route('admin.activity-categories.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = ActivityCategory::query()->withCount('activities')->find($id);
        if (! $category) {
            abort(404);
        }

        if ($category->activities_count > 0) {
            $noun = $category->activities_count === 1 ? 'activity' : 'activities';

            return redirect()->route('admin.activity-categories.index')
                ->with('categoryError', "Cannot delete \"{$category->Name}\" - it is used by {$category->activities_count} {$noun}. Reassign or delete those activities first.");
        }

        $category->delete();

        return redirect()->route('admin.activity-categories.index')->with('categoryMessage', 'Category successfully deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Name' => ['required', 'max:100'],
            'Description' => ['nullable', 'max:500'],
            'DisplayOrder' => ['nullable', 'integer'],
        ]);
    }
}
