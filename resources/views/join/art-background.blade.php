@extends('layouts.app')

@section('title', 'Art Background')

@php
    $applicantTypes = [
        'I am an artist and want to join the group' => 0,
        'I am an aspiring artist' => 1,
        'I am interested in learning about art' => 2,
        'I am a supporter or art enthusiast' => 3,
        'I am only interested in viewing the gallery at this time' => 4,
        'Other' => 5,
    ];
    $mediums = ['Painting', 'Sculpture', 'Photography', 'Digital Art', 'Illustration', 'Printmaking', 'Ceramics/Pottery', 'Mixed Media', 'Textile/Weaving', 'Woodcarving', 'Other'];
    $currentMediums = old('ArtMediums', $app->ArtMediums ? explode(', ', $app->ArtMediums) : []);
    $referralSources = [
        'Facebook' => 0, 'Instagram' => 1, 'Website' => 2, 'Friend or family member' => 3,
        'Current Sinaglahi member' => 4, 'Art exhibition' => 5, 'School' => 6, 'Community event' => 7, 'Other' => 8,
    ];
@endphp

@section('content')

<section class="section">
    <div class="container">
        <div class="join-steps mb-4">
            <div class="join-step join-step-done"><span class="join-step-dot">✓</span> <span class="join-step-label">Personal Info</span></div>
            <div class="join-step join-step-current"><span class="join-step-dot">2</span> <span class="join-step-label">Art Background</span></div>
            <div class="join-step"><span class="join-step-dot">3</span> <span class="join-step-label">Artworks</span></div>
            <div class="join-step"><span class="join-step-dot">4</span> <span class="join-step-label">Review &amp; Submit</span></div>
        </div>

        <div class="col-lg-8 mx-auto">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('join.art-background.store') }}" method="post" novalidate>
                @csrf

                <div class="form-section">
                    <h2>Art Background</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Are you an artist or simply interested in learning more about the group?</label>
                            <select name="ApplicantType" class="form-select" required>
                                <option value="">-- Select --</option>
                                @foreach ($applicantTypes as $label => $value)
                                    <option value="{{ $value }}" @selected(old('ApplicantType', $app->ApplicantType) == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">If Other, please specify</label>
                            <input name="ApplicantTypeOther" class="form-control" value="{{ old('ApplicantTypeOther', $app->ApplicantTypeOther) }}" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">What medium or mediums in art do you use?</label>
                            <div class="row">
                                @foreach ($mediums as $m)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="ArtMediums[]" value="{{ $m }}" id="med-{{ \Illuminate\Support\Str::slug($m) }}" @checked(in_array($m, $currentMediums))>
                                            <label class="form-check-label" for="med-{{ \Illuminate\Support\Str::slug($m) }}">{{ $m }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">If Other, please specify medium</label>
                            <input name="ArtMediumsOther" class="form-control" value="{{ old('ArtMediumsOther', $app->ArtMediumsOther) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">How long have you been practicing art?</label>
                            <input name="YearsPracticingArt" class="form-control" value="{{ old('YearsPracticingArt', $app->YearsPracticingArt) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">What type of art do you create?</label>
                            <input name="ArtType" class="form-control" value="{{ old('ArtType', $app->ArtType) }}" />
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="HasExhibitionExperience" value="1" id="hasExhib" @checked(old('HasExhibitionExperience', $app->HasExhibitionExperience))>
                                <label class="form-check-label" for="hasExhib">Have you participated in art exhibitions?</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="HasOtherOrganizationExperience" value="1" id="hasOtherOrg" @checked(old('HasOtherOrganizationExperience', $app->HasOtherOrganizationExperience))>
                                <label class="form-check-label" for="hasOtherOrg">Have you joined other art organizations?</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exhibition experience (if any)</label>
                            <textarea name="ExhibitionExperience" class="form-control" rows="2">{{ old('ExhibitionExperience', $app->ExhibitionExperience) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Other organization experience (if any)</label>
                            <textarea name="OtherOrganizationExperience" class="form-control" rows="2">{{ old('OtherOrganizationExperience', $app->OtherOrganizationExperience) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="SellsOrDisplaysArtwork" value="1" id="sells" @checked(old('SellsOrDisplaysArtwork', $app->SellsOrDisplaysArtwork))>
                                <label class="form-check-label" for="sells">Do you currently sell or display your artworks?</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Portfolio or social media link (optional)</label>
                            <input name="PortfolioUrl" class="form-control" value="{{ old('PortfolioUrl', $app->PortfolioUrl) }}" />
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Significant Questions</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Why do you want to join Sinaglahi Artists Group Nueva Vizcaya Inc.?</label>
                            <textarea name="WhyJoin" class="form-control" rows="4" required minlength="50">{{ old('WhyJoin', $app->WhyJoin) }}</textarea>
                            <p class="text-muted small mb-0">At least 50 characters.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">How did you learn about Sinaglahi Artists Group Nueva Vizcaya Inc.?</label>
                            <select name="HowLearnedAboutSinaglahi" class="form-select" required>
                                <option value="">-- Select --</option>
                                @foreach ($referralSources as $label => $value)
                                    <option value="{{ $value }}" @selected(old('HowLearnedAboutSinaglahi', $app->HowLearnedAboutSinaglahi) == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Please provide additional details (optional)</label>
                            <input name="HowLearnedAboutSinaglahiDetails" class="form-control" value="{{ old('HowLearnedAboutSinaglahiDetails', $app->HowLearnedAboutSinaglahiDetails) }}" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Please describe your artistic experience, interests, or goals.</label>
                            <textarea name="ArtExperience" class="form-control" rows="4" required minlength="50">{{ old('ArtExperience', $app->ArtExperience) }}</textarea>
                            <p class="text-muted small mb-0">At least 50 characters.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">What do you hope to contribute to or gain from Sinaglahi Artists Group Nueva Vizcaya Inc.?</label>
                            <textarea name="ContributionOrExpectations" class="form-control" rows="4" required minlength="30">{{ old('ContributionOrExpectations', $app->ContributionOrExpectations) }}</textarea>
                            <p class="text-muted small mb-0">At least 30 characters.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-brand btn-lg">Continue</button>
            </form>
        </div>
    </div>
</section>

@endsection
