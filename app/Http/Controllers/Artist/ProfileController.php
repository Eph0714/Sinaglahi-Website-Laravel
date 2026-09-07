<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistSocialLink;
use App\Services\ArtistLookupData;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Artist profile self-service editing - mirrors Areas/Artist/Controllers/
 * ProfileController in the .NET app. An artist may only ever touch their
 * own profile, never their own verification/active status.
 */
class ProfileController extends Controller
{
    private const MAX_SOCIAL_LINKS = 4;

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function edit(Request $request): View
    {
        /** @var Artist $artist */
        $artist = $request->attributes->get('artist');
        $links = ArtistSocialLink::query()->where('ArtistId', $artist->Id)->orderBy('Id')->get();

        [$specChecked, $specOther] = ArtistLookupData::parseSelections(ArtistLookupData::SPECIALIZATIONS, $artist->Specialization);
        [$medChecked, $medOther] = ArtistLookupData::parseSelections(ArtistLookupData::MEDIUMS, $artist->PreferredMedium);

        $socialLinks = $links->map(fn (ArtistSocialLink $l) => ['id' => $l->Id, 'platform' => $l->Platform, 'url' => $l->Url])->values()->all();
        while (count($socialLinks) < self::MAX_SOCIAL_LINKS) {
            $socialLinks[] = ['id' => null, 'platform' => '', 'url' => ''];
        }

        return view('artist.profile.edit', [
            'artist' => $artist,
            'contactNumber' => Auth::user()->PhoneNumber,
            'specChecked' => $specChecked,
            'specOther' => $specOther,
            'medChecked' => $medChecked,
            'medOther' => $medOther,
            'socialLinks' => $socialLinks,
            'provinces' => ArtistLookupData::PROVINCES,
            'specializations' => ArtistLookupData::SPECIALIZATIONS,
            'mediums' => ArtistLookupData::MEDIUMS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Artist $artist */
        $artist = $request->attributes->get('artist');

        $data = $request->validate([
            'ArtistName' => ['required', 'max:150'],
            'FullName' => ['required', 'max:150'],
            'Biography' => ['nullable', 'max:2000'],
            'ArtistStatement' => ['nullable', 'max:2000'],
            'Specializations' => ['nullable', 'array'],
            'SpecializationOther' => ['nullable', 'max:150'],
            'Mediums' => ['nullable', 'array'],
            'MediumOther' => ['nullable', 'max:150'],
            'Municipality' => ['nullable', 'max:150'],
            'Province' => ['nullable', 'max:150'],
            'DateOfBirth' => ['nullable', 'date', 'before_or_equal:today'],
            'ContactNumber' => ['nullable', 'max:30'],
            'YearsActive' => ['nullable', 'integer', 'min:0', 'max:100'],
            'IsProfilePublic' => ['nullable'],
            'ProfilePhoto' => ['nullable', 'image', 'max:10240'],
            'RemoveProfilePhoto' => ['nullable'],
            'SocialLinks' => ['nullable', 'array'],
        ]);

        $artist->ArtistName = trim($data['ArtistName']);
        $artist->FullName = trim($data['FullName']);
        $artist->Biography = ! empty($data['Biography']) ? trim($data['Biography']) : null;
        $artist->ArtistStatement = ! empty($data['ArtistStatement']) ? trim($data['ArtistStatement']) : null;
        $artist->Specialization = ArtistLookupData::combineSelections($data['Specializations'] ?? [], $data['SpecializationOther'] ?? null);
        $artist->PreferredMedium = ArtistLookupData::combineSelections($data['Mediums'] ?? [], $data['MediumOther'] ?? null);
        $artist->Municipality = ! empty($data['Municipality']) ? trim($data['Municipality']) : null;
        $artist->Province = ! empty($data['Province']) ? trim($data['Province']) : null;
        $artist->DateOfBirth = $data['DateOfBirth'] ?? null;
        $artist->YearsActive = $data['YearsActive'] ?? null;
        $artist->IsProfilePublic = $request->boolean('IsProfilePublic');
        $artist->UpdatedAt = now();

        $user = Auth::user();
        $newPhone = ! empty($data['ContactNumber']) ? trim($data['ContactNumber']) : null;
        if ($user->PhoneNumber !== $newPhone) {
            $user->forceFill(['PhoneNumber' => $newPhone])->save();
        }

        if ($request->boolean('RemoveProfilePhoto') && ! $request->hasFile('ProfilePhoto')) {
            $this->fileStorage->deletePublicImage($artist->ProfilePhotoPath);
            $artist->ProfilePhotoPath = null;
        }

        if ($request->hasFile('ProfilePhoto')) {
            $upload = $this->fileStorage->savePublicImage($request->file('ProfilePhoto'), 'artists');
            if ($upload->success) {
                $this->fileStorage->deletePublicImage($artist->ProfilePhotoPath);
                $artist->ProfilePhotoPath = $upload->storedPath;
            } else {
                return back()->withErrors(['ProfilePhoto' => $upload->error])->withInput();
            }
        }

        DB::transaction(function () use ($artist, $request) {
            $artist->save();

            ArtistSocialLink::query()->where('ArtistId', $artist->Id)->delete();
            foreach ((array) $request->input('SocialLinks', []) as $link) {
                $platform = trim((string) ($link['platform'] ?? ''));
                $url = trim((string) ($link['url'] ?? ''));
                if ($platform !== '' && $url !== '') {
                    ArtistSocialLink::query()->create([
                        'ArtistId' => $artist->Id,
                        'Platform' => $platform,
                        'Url' => $url,
                    ]);
                }
            }
        });

        return redirect()->route('artist.profile.edit')->with('profileSaved', 'Your profile has been updated.');
    }
}
