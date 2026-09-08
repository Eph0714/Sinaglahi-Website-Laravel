<?php

namespace App\Http\Controllers;

use App\Models\ApplicationArtwork;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatusHistory;
use App\Models\ArtMedium;
use App\Models\MembershipApplication;
use App\Services\ApplicationStatusPresentation;
use App\Services\ArtistLookupData;
use App\Services\ArtMediumHelper;
use App\Services\FileStorageService;
use App\Services\PersonalNameHelper;
use App\Services\ReferenceNumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * The public "Want to Join Sinaglahi?" multi-step application - mirrors
 * JoinController in the .NET app. The in-progress draft's database id is
 * kept server-side in the session, never in the URL, so one visitor can
 * never guess or tamper with another visitor's in-progress draft.
 */
class JoinController extends Controller
{
    private const DRAFT_SESSION_KEY = 'join_application_id';

    private const MIN_ARTWORKS = 5;

    private const MAX_ARTWORKS = 10;

    public function __construct(private readonly FileStorageService $fileStorage) {}

    public function index(Request $request): View
    {
        return view('join.index', ['hasDraftInProgress' => $request->session()->has(self::DRAFT_SESSION_KEY)]);
    }

    public function start(Request $request): RedirectResponse
    {
        $this->draft($request);

        return redirect()->route('join.personal-info');
    }

    // ---------------- Step 1: Personal Information + Affiliation ----------------

    public function personalInfo(Request $request): View
    {
        $app = $this->draft($request);

        return view('join.personal-info', [
            'app' => $app,
            'provinces' => ArtistLookupData::PROVINCES,
        ]);
    }

    public function personalInfoStore(Request $request): RedirectResponse|View
    {
        $app = $this->draft($request);

        $data = $request->validate([
            'FirstName' => ['required', 'max:100'],
            'MiddleInitial' => ['nullable', 'max:10'],
            'LastName' => ['required', 'max:100'],
            'ExtensionName' => ['nullable', 'max:20'],
            'PreferredName' => ['nullable', 'max:150'],
            'DateOfBirth' => ['required', 'date', 'before_or_equal:today'],
            'Gender' => ['required', 'integer', 'between:0,4'],
            'GenderOther' => ['nullable', 'max:150'],
            'Address' => ['required', 'max:300'],
            'Municipality' => ['required', 'max:150'],
            'Province' => ['required', 'max:150'],
            'ContactNumber' => ['required', 'max:30'],
            'Email' => ['required', 'email', 'max:200'],
            'CurrentArtOrganization' => ['required', 'max:200'],
            'School' => ['nullable', 'max:200'],
            'Occupation' => ['nullable', 'max:150'],
            'ProfilePhoto' => ['nullable', 'image', 'max:10240'],
        ]);

        if ((int) $data['Gender'] === 4 && trim((string) ($data['GenderOther'] ?? '')) === '') {
            return back()->withErrors(['GenderOther' => 'Please specify your gender.'])->withInput();
        }

        $age = Carbon::parse($data['DateOfBirth'])->age;
        if ($age < 10) {
            return back()->withErrors(['DateOfBirth' => 'You must be at least 10 years old to apply.'])->withInput();
        }

        // Save the photo immediately (before further checks) so it's never
        // silently dropped just because some other field was invalid.
        if ($request->hasFile('ProfilePhoto')) {
            $upload = $this->fileStorage->saveApplicationArtworkImage($request->file('ProfilePhoto'), $app->Id);
            if ($upload->success) {
                $this->fileStorage->deletePrivateImage($app->ProfilePhotoPath);
                $app->ProfilePhotoPath = $upload->storedPath;
                $app->save();
            } else {
                return back()->withErrors(['ProfilePhoto' => $upload->error])->withInput();
            }
        }

        if (! $app->ProfilePhotoPath) {
            return back()->withErrors(['ProfilePhoto' => 'Please upload a profile photo.'])->withInput();
        }

        $middleInitial = $data['MiddleInitial'] ?? null;
        $extensionName = $data['ExtensionName'] ?? null;
        $genderOther = $data['GenderOther'] ?? null;

        $app->FirstName = trim($data['FirstName']);
        $app->MiddleInitial = ! empty($middleInitial) ? trim($middleInitial) : null;
        $app->LastName = trim($data['LastName']);
        $app->ExtensionName = ! empty($extensionName) ? trim($extensionName) : null;
        $app->FullName = PersonalNameHelper::composeFullName($data['FirstName'], $middleInitial, $data['LastName'], $extensionName);
        $app->PreferredName = ! empty($data['PreferredName']) ? trim($data['PreferredName']) : null;
        $app->DateOfBirth = $data['DateOfBirth'];
        $app->Age = $age;
        $app->Gender = (int) $data['Gender'];
        $app->GenderOther = (int) $data['Gender'] === 4 ? trim((string) $genderOther) : null;
        $app->Address = trim($data['Address']);
        $app->Municipality = trim($data['Municipality']);
        $app->Province = trim($data['Province']);
        $app->ContactNumber = trim($data['ContactNumber']);
        $app->Email = trim($data['Email']);
        $app->CurrentArtOrganization = trim($data['CurrentArtOrganization']);
        $app->School = ! empty($data['School']) ? trim($data['School']) : null;
        $app->Occupation = ! empty($data['Occupation']) ? trim($data['Occupation']) : null;
        $app->UpdatedAt = now();
        $app->save();

        return redirect()->route('join.art-background');
    }

    public function personalInfoPhoto(Request $request)
    {
        $draftId = $request->session()->get(self::DRAFT_SESSION_KEY);
        if (! $draftId) {
            abort(404);
        }
        $app = MembershipApplication::query()->find($draftId);
        if (! $app || ! $app->ProfilePhotoPath) {
            abort(404);
        }
        $result = $this->fileStorage->readPrivateImage($app->ProfilePhotoPath);
        if (! $result) {
            abort(404);
        }

        return response($result['bytes'])->header('Content-Type', $result['contentType']);
    }

    // ---------------- Step 2: Art Background + Significant Questions ----------------

    public function artBackground(Request $request): View|RedirectResponse
    {
        $app = $this->draft($request);
        if (! $this->hasCompletedPersonalInfo($app)) {
            return redirect()->route('join.personal-info');
        }

        return view('join.art-background', ['app' => $app]);
    }

    public function artBackgroundStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ApplicantType' => ['required', 'integer', 'between:0,5'],
            'ApplicantTypeOther' => ['nullable', 'max:150'],
            'ArtMediums' => ['required', 'array', 'min:1'],
            'ArtMediumsOther' => ['nullable', 'max:150'],
            'YearsPracticingArt' => ['nullable', 'max:150'],
            'ArtType' => ['nullable', 'max:200'],
            'HasExhibitionExperience' => ['nullable'],
            'ExhibitionExperience' => ['nullable', 'max:2000'],
            'HasOtherOrganizationExperience' => ['nullable'],
            'OtherOrganizationExperience' => ['nullable', 'max:2000'],
            'SellsOrDisplaysArtwork' => ['nullable'],
            'PortfolioUrl' => ['nullable', 'url', 'max:500'],
            'WhyJoin' => ['required', 'min:50', 'max:4000'],
            'HowLearnedAboutSinaglahi' => ['required', 'integer', 'between:0,8'],
            'HowLearnedAboutSinaglahiDetails' => ['nullable', 'max:1000'],
            'ArtExperience' => ['required', 'min:50', 'max:4000'],
            'ContributionOrExpectations' => ['required', 'min:30', 'max:4000'],
        ]);

        if ((int) $data['ApplicantType'] === 5 && trim((string) ($data['ApplicantTypeOther'] ?? '')) === '') {
            return back()->withErrors(['ApplicantTypeOther' => 'Please specify.'])->withInput();
        }
        if (in_array('Other', $data['ArtMediums'], true) && trim((string) ($data['ArtMediumsOther'] ?? '')) === '') {
            return back()->withErrors(['ArtMediumsOther' => 'Please specify the other medium.'])->withInput();
        }

        $app = $this->draft($request);
        if (! $this->hasCompletedPersonalInfo($app)) {
            return redirect()->route('join.personal-info');
        }

        $hasExhibition = $request->boolean('HasExhibitionExperience');
        $hasOtherOrg = $request->boolean('HasOtherOrganizationExperience');

        $app->ApplicantType = (int) $data['ApplicantType'];
        $app->ApplicantTypeOther = (int) $data['ApplicantType'] === 5 ? trim((string) ($data['ApplicantTypeOther'] ?? '')) : null;
        $app->ArtMediums = implode(', ', $data['ArtMediums']);
        $app->ArtMediumsOther = in_array('Other', $data['ArtMediums'], true) ? trim((string) ($data['ArtMediumsOther'] ?? '')) : null;
        $app->YearsPracticingArt = ! empty($data['YearsPracticingArt']) ? trim($data['YearsPracticingArt']) : null;
        $app->ArtType = ! empty($data['ArtType']) ? trim($data['ArtType']) : null;
        $app->HasExhibitionExperience = $hasExhibition;
        $app->ExhibitionExperience = $hasExhibition && ! empty($data['ExhibitionExperience']) ? trim($data['ExhibitionExperience']) : null;
        $app->HasOtherOrganizationExperience = $hasOtherOrg;
        $app->OtherOrganizationExperience = $hasOtherOrg && ! empty($data['OtherOrganizationExperience']) ? trim($data['OtherOrganizationExperience']) : null;
        $app->SellsOrDisplaysArtwork = $request->boolean('SellsOrDisplaysArtwork');
        $app->PortfolioUrl = ! empty($data['PortfolioUrl']) ? trim($data['PortfolioUrl']) : null;
        $app->WhyJoin = trim($data['WhyJoin']);
        $app->HowLearnedAboutSinaglahi = (int) $data['HowLearnedAboutSinaglahi'];
        $app->HowLearnedAboutSinaglahiDetails = ! empty($data['HowLearnedAboutSinaglahiDetails']) ? trim($data['HowLearnedAboutSinaglahiDetails']) : null;
        $app->ArtExperience = trim($data['ArtExperience']);
        $app->ContributionOrExpectations = trim($data['ContributionOrExpectations']);
        $app->UpdatedAt = now();
        $app->save();

        return redirect()->route('join.artworks');
    }

    // ---------------- Step 3: Artworks (5-10) ----------------

    public function artworks(Request $request): View|RedirectResponse
    {
        $app = $this->draft($request);
        if (! $this->hasCompletedArtBackground($app)) {
            return redirect()->route('join.art-background');
        }

        return view('join.artworks', [
            'app' => $app,
            'existingArtworks' => ApplicationArtwork::query()->where('MembershipApplicationId', $app->Id)->orderBy('Id')->get(),
            'minArtworks' => self::MIN_ARTWORKS,
            'maxArtworks' => self::MAX_ARTWORKS,
            'mediumOptions' => ArtMediumHelper::getActiveOptions(),
        ]);
    }

    public function addArtwork(Request $request): RedirectResponse
    {
        $app = $this->draft($request);

        $data = $request->validate([
            'Image' => ['required', 'image', 'max:10240'],
            'Title' => ['required', 'max:200'],
            'MediumId' => ['required', 'integer'],
            'CustomMedium' => ['nullable', 'max:200'],
            'Year' => ['nullable', 'integer', 'between:1900,2100'],
            'Size' => ['nullable', 'max:100'],
            'Description' => ['nullable', 'max:2000'],
        ]);

        $currentCount = ApplicationArtwork::query()->where('MembershipApplicationId', $app->Id)->count();
        if ($currentCount >= self::MAX_ARTWORKS) {
            return back()->withErrors(['Image' => 'You may upload at most '.self::MAX_ARTWORKS.' artworks.']);
        }

        $medium = ArtMedium::query()->find($data['MediumId']);
        if (! $medium) {
            return back()->withErrors(['MediumId' => 'Please select a valid medium.']);
        }
        if ($medium->IsOtherOption && trim((string) ($data['CustomMedium'] ?? '')) === '') {
            return back()->withErrors(['CustomMedium' => 'Please specify the medium.']);
        }

        $upload = $this->fileStorage->saveApplicationArtworkImage($request->file('Image'), $app->Id);
        if (! $upload->success) {
            return back()->withErrors(['Image' => $upload->error]);
        }

        ApplicationArtwork::query()->create([
            'MembershipApplicationId' => $app->Id,
            'Title' => trim($data['Title']),
            'MediumId' => $medium->Id,
            'CustomMedium' => $medium->IsOtherOption ? trim((string) ($data['CustomMedium'] ?? '')) : null,
            'Medium' => ArtMediumHelper::composeDisplayMedium($medium->Name, $medium->IsOtherOption, $data['CustomMedium'] ?? null),
            'Year' => $data['Year'] ?? null,
            'Size' => ! empty($data['Size']) ? trim($data['Size']) : null,
            'Description' => ! empty($data['Description']) ? trim($data['Description']) : null,
            'ImagePath' => $upload->storedPath,
            'ThumbnailPath' => $upload->thumbnailPath,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('join.artworks')->with('artworkAdded', 'Artwork added.');
    }

    public function removeArtwork(Request $request, int $id): RedirectResponse
    {
        $app = $this->draft($request);
        $artwork = ApplicationArtwork::query()->where('Id', $id)->where('MembershipApplicationId', $app->Id)->first();
        if ($artwork) {
            $this->fileStorage->deletePrivateImage($artwork->ImagePath);
            $this->fileStorage->deletePrivateImage($artwork->ThumbnailPath);
            $artwork->delete();
        }

        return redirect()->route('join.artworks');
    }

    public function artworkImage(Request $request, int $id)
    {
        $draftId = $request->session()->get(self::DRAFT_SESSION_KEY);
        if (! $draftId) {
            abort(404);
        }
        $artwork = ApplicationArtwork::query()->where('Id', $id)->where('MembershipApplicationId', $draftId)->first();
        if (! $artwork) {
            abort(404);
        }
        $result = $this->fileStorage->readPrivateImage($artwork->ThumbnailPath ?? $artwork->ImagePath);
        if (! $result) {
            abort(404);
        }

        return response($result['bytes'])->header('Content-Type', $result['contentType']);
    }

    // ---------------- Step 4: Consent, review, submit ----------------

    public function consent(Request $request): View|RedirectResponse
    {
        $app = $this->draft($request);
        if (! $this->hasCompletedArtBackground($app)) {
            return redirect()->route('join.art-background');
        }

        $artworkCount = ApplicationArtwork::query()->where('MembershipApplicationId', $app->Id)->count();
        if ($artworkCount < self::MIN_ARTWORKS) {
            return redirect()->route('join.artworks');
        }

        return view('join.consent', [
            'app' => $app,
            'artworks' => ApplicationArtwork::query()->where('MembershipApplicationId', $app->Id)->orderBy('Id')->get(),
        ]);
    }

    public function submit(Request $request): RedirectResponse|View
    {
        $app = $this->draft($request);

        if (! $request->boolean('ApplicantConsent') || ! $request->boolean('PrivacyConsent')) {
            return back()->withErrors(['ApplicantConsent' => 'You must certify accuracy and agree to the privacy consent to submit your application.']);
        }

        $artworkCount = ApplicationArtwork::query()->where('MembershipApplicationId', $app->Id)->count();
        if ($artworkCount < self::MIN_ARTWORKS) {
            return back()->withErrors(['Image' => 'Please upload at least '.self::MIN_ARTWORKS.' artworks before submitting your application.']);
        }
        if (! $this->hasCompletedArtBackground($app)) {
            return back()->withErrors(['ApplicantConsent' => 'Please complete all required steps before submitting.']);
        }

        $wasRevisionResubmission = (int) $app->Status === MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED;

        $app->ApplicantConsent = true;
        $app->PrivacyConsent = true;
        $app->ConsentDate = now();
        $app->ReferenceNumber = $app->ReferenceNumber ?: ReferenceNumberGenerator::generate();
        $app->Status = $wasRevisionResubmission ? MembershipApplication::STATUS_UNDER_REVIEW : MembershipApplication::STATUS_PENDING_REVIEW;
        if (! $wasRevisionResubmission) {
            $app->SubmittedAt = now();
        }
        $app->IPAddress = $request->ip();
        $app->UpdatedAt = now();
        $app->save();

        ApplicationStatusHistory::query()->create([
            'MembershipApplicationId' => $app->Id,
            'Status' => $app->Status,
            'Remarks' => $wasRevisionResubmission ? 'Revised application resubmitted by applicant.' : 'Application received.',
            'ChangedByUserId' => null,
            'ChangedAt' => now(),
        ]);

        $request->session()->forget(self::DRAFT_SESSION_KEY);
        $request->session()->flash('submitted_application_id', $app->Id);

        return redirect()->route('join.confirmation');
    }

    public function confirmation(Request $request): View|RedirectResponse
    {
        $id = $request->session()->get('submitted_application_id');
        if (! $id) {
            return redirect()->route('join.index');
        }
        $app = MembershipApplication::query()->find($id);
        if (! $app) {
            return redirect()->route('join.index');
        }
        $request->session()->reflash();

        return view('join.confirmation', [
            'referenceNumber' => $app->ReferenceNumber,
            'applicantName' => $app->FullName,
            'submittedAt' => $app->SubmittedAt,
            'status' => ApplicationStatusPresentation::label((int) $app->Status),
        ]);
    }

    // ---------------- Check Application Status (self-service) ----------------

    public function checkStatus(Request $request): View
    {
        return view('join.check-status');
    }

    public function checkStatusSubmit(Request $request): View
    {
        $key = 'statuscheck:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return view('join.check-status')->withErrors(['general' => 'Too many status-check attempts. Please wait a few minutes and try again, or contact the Sinaglahi Artists Group administration.']);
        }
        RateLimiter::hit($key, 300);

        $noReferenceNumber = $request->boolean('NoReferenceNumber');

        $data = $request->validate([
            'ReferenceNumber' => [$noReferenceNumber ? 'nullable' : 'required', 'max:40'],
            'ContactNumber' => ['required', 'max:30'],
            'FullName' => [$noReferenceNumber ? 'required' : 'nullable', 'max:150'],
            'Email' => [$noReferenceNumber ? 'required' : 'nullable', 'email', 'max:200'],
        ]);

        $contactDigits = $this->normalizeDigits($data['ContactNumber']);

        if (! $noReferenceNumber) {
            $referenceNumber = trim($data['ReferenceNumber']);
            $candidate = MembershipApplication::query()
                ->where('ReferenceNumber', $referenceNumber)
                ->where('Status', '!=', MembershipApplication::STATUS_DRAFT)
                ->first();

            $app = ($candidate && $this->normalizeDigits($candidate->ContactNumber) === $contactDigits) ? $candidate : null;

            if (! $app) {
                return view('join.check-status')->withErrors(['general' => "We couldn't find an application matching that reference number and registered mobile number. Please double-check both and try again, or contact the Sinaglahi Artists Group administration."]);
            }
        } else {
            $fullName = trim($data['FullName']);
            $email = trim($data['Email']);

            $matches = MembershipApplication::query()
                ->where('Status', '!=', MembershipApplication::STATUS_DRAFT)
                ->whereRaw('LOWER(Email) = ?', [mb_strtolower($email)])
                ->get()
                ->filter(fn (MembershipApplication $a) => $this->normalizeDigits($a->ContactNumber) === $contactDigits
                    && mb_strtolower(trim($a->FullName)) === mb_strtolower($fullName));

            if ($matches->isEmpty()) {
                return view('join.check-status')->withErrors(['general' => 'No application was found using this registered mobile number. Please verify the number and try again. If you still need assistance, contact Sinaglahi Artists administration.']);
            }
            if ($matches->count() > 1) {
                return view('join.check-status')->withErrors(['general' => 'More than one application matches this information. Please check your status using your Application Reference Number instead, or contact Sinaglahi Artists administration.']);
            }

            $app = $matches->first();
        }

        $notes = ApplicationNote::query()->where('MembershipApplicationId', $app->Id)->where('IsApplicantVisible', true)
            ->orderByDesc('CreatedAt')->pluck('Note');

        $history = ApplicationStatusHistory::query()->where('MembershipApplicationId', $app->Id)->orderBy('ChangedAt')->get();

        return view('join.status-result', [
            'referenceNumber' => $app->ReferenceNumber,
            'applicantName' => $app->FullName,
            'maskedContactNumber' => $this->maskContactNumber($app->ContactNumber),
            'submittedAt' => $app->SubmittedAt,
            'lastUpdatedAt' => $app->UpdatedAt,
            'status' => (int) $app->Status,
            'statusLabel' => ApplicationStatusPresentation::label((int) $app->Status),
            'statusDescription' => ApplicationStatusPresentation::description((int) $app->Status),
            'timeline' => ApplicationStatusPresentation::buildTimeline((int) $app->Status),
            'hasArtistAccount' => (bool) $app->ConvertedArtistId,
            'applicantVisibleNotes' => $notes,
            'history' => $history,
            'canEditForRevision' => (int) $app->Status === MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED,
            'referenceNumberForEdit' => $app->ReferenceNumber,
            'contactNumberForEdit' => $app->ContactNumber,
        ]);
    }

    public function editApplication(Request $request): RedirectResponse
    {
        $key = 'statuscheck:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return redirect()->route('join.checkStatus')->with('checkStatusError', 'Too many attempts. Please wait a few minutes and try again.');
        }
        RateLimiter::hit($key, 300);

        $refNum = trim((string) $request->input('referenceNumber'));
        $contactDigits = $this->normalizeDigits($request->input('contactNumber'));

        $app = MembershipApplication::query()
            ->where('ReferenceNumber', $refNum)
            ->where('Status', MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED)
            ->first();

        if (! $app || $this->normalizeDigits($app->ContactNumber) !== $contactDigits) {
            return redirect()->route('join.checkStatus')->with('checkStatusError', "We couldn't verify that application for editing. Please check your status again.");
        }

        $request->session()->put(self::DRAFT_SESSION_KEY, $app->Id);

        return redirect()->route('join.personal-info');
    }

    // ---------------- Helpers ----------------

    private function normalizeDigits(?string $value): string
    {
        return preg_replace('/\D/', '', (string) $value) ?? '';
    }

    private function maskContactNumber(string $contactNumber): string
    {
        $digits = $this->normalizeDigits($contactNumber);
        if (strlen($digits) <= 7) {
            return str_repeat('•', strlen($digits));
        }
        $prefix = substr($digits, 0, 4);
        $suffix = substr($digits, -3);

        return $prefix.str_repeat('•', strlen($digits) - 7).$suffix;
    }

    private function draft(Request $request): MembershipApplication
    {
        $id = $request->session()->get(self::DRAFT_SESSION_KEY);
        if ($id) {
            $existing = MembershipApplication::query()->where('Id', $id)
                ->whereIn('Status', [MembershipApplication::STATUS_DRAFT, MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED])
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        $app = MembershipApplication::query()->create([
            'Status' => MembershipApplication::STATUS_DRAFT,
            'ReferenceNumber' => ReferenceNumberGenerator::generate(),
            'FullName' => '',
            'Age' => 0,
            'Gender' => 0,
            'Address' => '',
            'Municipality' => '',
            'Province' => '',
            'ContactNumber' => '',
            'Email' => '',
            'CurrentArtOrganization' => '',
            'ApplicantType' => 0,
            'ArtMediums' => '',
            'HasExhibitionExperience' => false,
            'HasOtherOrganizationExperience' => false,
            'SellsOrDisplaysArtwork' => false,
            'WhyJoin' => '',
            'HowLearnedAboutSinaglahi' => 0,
            'ArtExperience' => '',
            'ContributionOrExpectations' => '',
            'ApplicantConsent' => false,
            'PrivacyConsent' => false,
            'SubmittedAt' => now(),
            'CreatedAt' => now(),
        ]);

        $request->session()->put(self::DRAFT_SESSION_KEY, $app->Id);

        return $app;
    }

    private function hasCompletedPersonalInfo(MembershipApplication $app): bool
    {
        return ! empty($app->FullName) && ! empty($app->Email);
    }

    private function hasCompletedArtBackground(MembershipApplication $app): bool
    {
        return $this->hasCompletedPersonalInfo($app) && ! empty($app->WhyJoin) && ! empty($app->ArtExperience);
    }
}
