<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationArtwork;
use App\Models\ArtMedium;
use App\Models\Artwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super Admin management of the standardized Medium list - mirrors
 * Areas/Admin/Controllers/ArtMediumsController in the .NET app. The single
 * "Other" row can be renamed/reordered but never deactivated or deleted; a
 * medium already used by any artwork can only be deactivated, never deleted.
 */
class ArtMediumsController extends Controller
{
    public function index(): View
    {
        $mediums = ArtMedium::query()
            ->withCount(['artworks as UsageCount'])
            ->orderBy('DisplayOrder')->orderBy('Name')
            ->get()
            ->map(function (ArtMedium $m) {
                $m->UsageCount += ApplicationArtwork::query()->where('MediumId', $m->Id)->count();

                return $m;
            });

        return view('admin.art-mediums.index', ['mediums' => $mediums]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['Name' => ['required', 'max:100']]);

        $maxOrder = ArtMedium::query()->max('DisplayOrder') ?? 0;
        ArtMedium::query()->create([
            'Name' => trim($data['Name']),
            'DisplayOrder' => $maxOrder + 1,
            'IsActive' => true,
            'IsOtherOption' => false,
        ]);

        return redirect()->route('admin.art-mediums.index')->with('mediumMessage', 'Medium added.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Id' => ['required', 'integer'],
            'Name' => ['required', 'max:100'],
        ]);

        $medium = ArtMedium::query()->find($data['Id']);
        if (! $medium) {
            abort(404);
        }

        $medium->Name = trim($data['Name']);
        // The "Other" row must always stay active - structurally required.
        if (! $medium->IsOtherOption) {
            $medium->IsActive = $request->boolean('IsActive');
        }
        $medium->save();

        return redirect()->route('admin.art-mediums.index')->with('mediumMessage', 'Medium updated.');
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $medium = ArtMedium::query()->find($id);
        if (! $medium) {
            return redirect()->route('admin.art-mediums.index');
        }

        if ($medium->IsOtherOption) {
            return redirect()->route('admin.art-mediums.index')
                ->with('mediumError', '"Other – Specify Medium" cannot be deactivated - it is required for artists to describe unusual materials.');
        }

        $medium->IsActive = ! $medium->IsActive;
        $medium->save();

        return redirect()->route('admin.art-mediums.index');
    }

    public function move(Request $request, int $id): RedirectResponse
    {
        $direction = $request->input('direction');
        $mediums = ArtMedium::query()->orderBy('DisplayOrder')->orderBy('Name')->get();
        $index = $mediums->search(fn (ArtMedium $m) => $m->Id === $id);

        if ($index === false) {
            return redirect()->route('admin.art-mediums.index');
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= $mediums->count()) {
            return redirect()->route('admin.art-mediums.index');
        }

        $a = $mediums->get($index);
        $b = $mediums->get($swapWith);
        [$a->DisplayOrder, $b->DisplayOrder] = [$b->DisplayOrder, $a->DisplayOrder];
        $a->save();
        $b->save();

        return redirect()->route('admin.art-mediums.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $medium = ArtMedium::query()->find($id);
        if (! $medium) {
            return redirect()->route('admin.art-mediums.index');
        }

        if ($medium->IsOtherOption) {
            return redirect()->route('admin.art-mediums.index')->with('mediumError', '"Other – Specify Medium" cannot be deleted.');
        }

        $inUse = Artwork::query()->where('MediumId', $id)->exists() || ApplicationArtwork::query()->where('MediumId', $id)->exists();
        if ($inUse) {
            return redirect()->route('admin.art-mediums.index')
                ->with('mediumError', "\"{$medium->Name}\" is used by existing artwork records and cannot be deleted - deactivate it instead.");
        }

        $medium->delete();

        return redirect()->route('admin.art-mediums.index')->with('mediumMessage', 'Medium deleted.');
    }
}
