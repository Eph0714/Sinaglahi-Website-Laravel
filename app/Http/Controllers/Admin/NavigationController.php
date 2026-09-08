<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Navigation Menu / Footer link management - mirrors Areas/Admin/Controllers/NavigationController. */
class NavigationController extends Controller
{
    public function index(): View
    {
        $items = NavigationMenuItem::query()->orderBy('Location')->orderBy('DisplayOrder')->get();

        return view('admin.navigation.index', [
            'itemsByLocation' => $items->groupBy('Location'),
            'locations' => [
                NavigationMenuItem::LOCATION_MAIN_NAV => 'MainNav',
                NavigationMenuItem::LOCATION_FOOTER_EXPLORE => 'FooterExplore',
                NavigationMenuItem::LOCATION_FOOTER_MEMBERSHIP => 'FooterMembership',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        NavigationMenuItem::query()->create([
            'Label' => trim($data['Label']),
            'Url' => trim($data['Url']),
            'IsExternal' => $request->boolean('IsExternal'),
            'OpenInNewTab' => $request->boolean('OpenInNewTab'),
            'Location' => $data['Location'],
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'IsActive' => $request->boolean('IsActive'),
        ]);

        return redirect()->route('admin.navigation.index')->with('menuMessage', 'Menu item added.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $this->validated($request, requireId: true);

        $item = NavigationMenuItem::query()->find($data['Id']);
        if (! $item) {
            abort(404);
        }

        $item->Label = trim($data['Label']);
        $item->Url = trim($data['Url']);
        $item->IsExternal = $request->boolean('IsExternal');
        $item->OpenInNewTab = $request->boolean('OpenInNewTab');
        $item->Location = $data['Location'];
        $item->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $item->IsActive = $request->boolean('IsActive');
        $item->save();

        return redirect()->route('admin.navigation.index')->with('menuMessage', 'Menu item updated.');
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $item = NavigationMenuItem::query()->find($id);
        if ($item) {
            $item->IsActive = ! $item->IsActive;
            $item->save();
        }

        return redirect()->route('admin.navigation.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        NavigationMenuItem::query()->where('Id', $id)->delete();

        return redirect()->route('admin.navigation.index')->with('menuMessage', 'Menu item permanently deleted.');
    }

    private function validated(Request $request, bool $requireId = false): array
    {
        $rules = [
            'Label' => ['required', 'max:100'],
            'Url' => ['required', 'max:500'],
            'Location' => ['required', 'integer', 'between:0,2'],
            'DisplayOrder' => ['nullable', 'integer'],
        ];
        if ($requireId) {
            $rules['Id'] = ['required', 'integer'];
        }

        return $request->validate($rules);
    }
}
