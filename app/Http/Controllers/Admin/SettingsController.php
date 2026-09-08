<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use App\Services\ArtistLookupData;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Website Settings / CMS hub - mirrors Areas/Admin/Controllers/
 * SettingsController in the .NET app: general info, contact, logos,
 * default SEO, and social links (the site-wide singleton record).
 */
class SettingsController extends Controller
{
    private const LOGO_SUBFOLDER = 'site';

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => SiteSetting::current(),
            'provinces' => ArtistLookupData::PROVINCES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $s = SiteSetting::current();

        $data = $request->validate([
            'WebsiteName' => ['required', 'max:150'],
            'OrganizationName' => ['required', 'max:150'],
            'ShortName' => ['nullable', 'max:50'],
            'Tagline' => ['nullable', 'max:250'],
            'Description' => ['nullable', 'max:1000'],
            'ContactEmail' => ['nullable', 'email', 'max:200'],
            'ContactNumber' => ['nullable', 'max:30'],
            'MobileNumber' => ['nullable', 'max:30'],
            'Address' => ['nullable', 'max:300'],
            'Municipality' => ['nullable', 'max:150'],
            'Province' => ['nullable', 'max:150'],
            'Country' => ['nullable', 'max:100'],
            'ZipCode' => ['nullable', 'max:20'],
            'GoogleMapsUrl' => ['nullable', 'max:1000'],
            'OfficeHours' => ['nullable', 'max:2000'],
            'CopyrightText' => ['nullable', 'max:300'],
            'FooterText' => ['nullable', 'max:2000'],
            'SeoTitle' => ['nullable', 'max:200'],
            'SeoMetaDescription' => ['nullable', 'max:300'],
            'SeoKeywords' => ['nullable', 'max:500'],
            'Logo' => ['nullable', 'image', 'max:10240'],
            'LogoDark' => ['nullable', 'image', 'max:10240'],
            'Favicon' => ['nullable', 'image', 'max:10240'],
            'MobileLogo' => ['nullable', 'image', 'max:10240'],
            'SeoDefaultOgImage' => ['nullable', 'image', 'max:10240'],
        ]);

        foreach ($data as $key => $value) {
            if (in_array($key, ['Logo', 'LogoDark', 'Favicon', 'MobileLogo', 'SeoDefaultOgImage'], true)) {
                continue;
            }
            $s->{$key} = $value !== null ? trim($value) : null;
        }

        $uploadIfProvided = function (?string $field, string $pathColumn) use ($request, $s) {
            if (! $request->hasFile($field)) {
                return;
            }
            $upload = $this->fileStorage->savePublicImage($request->file($field), self::LOGO_SUBFOLDER);
            if ($upload->success) {
                $s->{$pathColumn} = $upload->storedPath;
            }
        };

        $uploadIfProvided('Logo', 'LogoPath');
        $uploadIfProvided('LogoDark', 'LogoDarkPath');
        $uploadIfProvided('Favicon', 'FaviconPath');
        $uploadIfProvided('MobileLogo', 'MobileLogoPath');
        $uploadIfProvided('SeoDefaultOgImage', 'SeoDefaultOgImagePath');

        $s->UpdatedAt = now();
        $s->UpdatedByUserId = auth()->id();
        $s->save();

        return redirect()->route('admin.settings.index')->with('settingsMessage', 'Website settings saved.');
    }

    public function deleteLogo(Request $request): RedirectResponse
    {
        $s = SiteSetting::current();
        $field = $request->input('field');

        $columns = [
            'Logo' => 'LogoPath',
            'LogoDark' => 'LogoDarkPath',
            'Favicon' => 'FaviconPath',
            'MobileLogo' => 'MobileLogoPath',
        ];

        if (isset($columns[$field])) {
            $column = $columns[$field];
            $this->fileStorage->deletePublicImage($s->{$column});
            $s->{$column} = null;
            $s->save();
        }

        return redirect()->route('admin.settings.index')->with('settingsMessage', 'Image removed.');
    }

    // ---------------- Social Links ----------------

    public function socialLinks(): View
    {
        return view('admin.settings.social-links', [
            'links' => SocialLink::query()->orderBy('DisplayOrder')->get(),
        ]);
    }

    public function createSocialLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Platform' => ['required', 'max:50'],
            'Url' => ['required', 'max:500'],
            'DisplayOrder' => ['nullable', 'integer'],
        ]);

        SocialLink::query()->create([
            'Platform' => trim($data['Platform']),
            'Url' => trim($data['Url']),
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'IsActive' => $request->boolean('IsActive'),
        ]);

        return redirect()->route('admin.settings.social-links')->with('settingsMessage', 'Social link added.');
    }

    public function updateSocialLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Id' => ['required', 'integer'],
            'Platform' => ['required', 'max:50'],
            'Url' => ['required', 'max:500'],
            'DisplayOrder' => ['nullable', 'integer'],
        ]);

        $link = SocialLink::query()->find($data['Id']);
        if (! $link) {
            abort(404);
        }

        $link->Platform = trim($data['Platform']);
        $link->Url = trim($data['Url']);
        $link->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $link->IsActive = $request->boolean('IsActive');
        $link->save();

        return redirect()->route('admin.settings.social-links')->with('settingsMessage', 'Social link updated.');
    }

    public function destroySocialLink(int $id): RedirectResponse
    {
        SocialLink::query()->where('Id', $id)->delete();

        return redirect()->route('admin.settings.social-links')->with('settingsMessage', 'Social link permanently deleted.');
    }
}
