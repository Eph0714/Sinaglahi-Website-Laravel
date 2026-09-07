@extends('layouts.artist')

@section('title', 'Edit Profile')

@section('content')

<h1 class="h3 mb-4">Edit Artist Profile</h1>

@if (session('profileSaved'))
    <div class="alert alert-success">{{ session('profileSaved') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('artist.profile.update') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Photo</h2>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <label class="form-label d-block">Profile Photo</label>
                @if ($artist->ProfilePhotoPath)
                    <img src="{{ $artist->ProfilePhotoPath }}" class="artist-avatar mb-2" alt="Current profile photo" />
                @endif
                <input name="ProfilePhoto" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
                <p class="text-muted small mt-1 mb-0">Square photo recommended.</p>
                @if ($artist->ProfilePhotoPath)
                    <div class="form-check mt-1 text-start">
                        <input name="RemoveProfilePhoto" type="checkbox" value="1" class="form-check-input" id="removePhoto" />
                        <label class="form-check-label" for="removePhoto">Remove current profile photo</label>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Profile Information</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Artist Name / Display Name</label>
                <input name="ArtistName" class="form-control" value="{{ old('ArtistName', $artist->ArtistName) }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input name="FullName" class="form-control" value="{{ old('FullName', $artist->FullName) }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Province</label>
                <select name="Province" class="form-select" data-province-select="Province">
                    <option value="">-- Select Province --</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}" @selected($artist->Province === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Municipality / City</label>
                <div data-municipality-wrap="Province" data-field-name="Municipality" data-current-value="{{ $artist->Municipality }}"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Birthday</label>
                <input name="DateOfBirth" type="date" class="form-control" max="{{ now()->toDateString() }}" value="{{ optional($artist->DateOfBirth)->format('Y-m-d') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input name="ContactNumber" class="form-control" value="{{ old('ContactNumber', $contactNumber) }}" />
            </div>

            <div class="col-md-6">
                <label class="form-label">Art Specialization</label>
                <p class="text-muted small mb-1">Check all that apply. If none fit, check "Others" and specify.</p>
                @foreach ($specializations as $s)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Specializations[]" value="{{ $s }}" id="pspec-{{ \Illuminate\Support\Str::slug($s) }}" @checked(in_array($s, $specChecked)) />
                        <label class="form-check-label" for="pspec-{{ \Illuminate\Support\Str::slug($s) }}">{{ $s }}</label>
                    </div>
                @endforeach
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pSpecOthersCheck" data-others-toggle="pSpecializationOtherWrap" @checked($specOther) />
                    <label class="form-check-label" for="pSpecOthersCheck">Others</label>
                </div>
                <div id="pSpecializationOtherWrap" class="mt-1" @if(!$specOther) hidden @endif>
                    <input name="SpecializationOther" class="form-control" placeholder="Please specify" value="{{ $specOther }}" />
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Preferred Medium</label>
                <p class="text-muted small mb-1">Check all that apply. If none fit, check "Others" and specify.</p>
                @foreach ($mediums as $m)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Mediums[]" value="{{ $m }}" id="pmed-{{ \Illuminate\Support\Str::slug($m) }}" @checked(in_array($m, $medChecked)) />
                        <label class="form-check-label" for="pmed-{{ \Illuminate\Support\Str::slug($m) }}">{{ $m }}</label>
                    </div>
                @endforeach
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pMedOthersCheck" data-others-toggle="pMediumOtherWrap" @checked($medOther) />
                    <label class="form-check-label" for="pMedOthersCheck">Others</label>
                </div>
                <div id="pMediumOtherWrap" class="mt-1" @if(!$medOther) hidden @endif>
                    <input name="MediumOther" class="form-control" placeholder="Please specify" value="{{ $medOther }}" />
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Years Active</label>
                <input name="YearsActive" type="number" class="form-control" value="{{ old('YearsActive', $artist->YearsActive) }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Biography</label>
                <textarea name="Biography" class="form-control" rows="3">{{ old('Biography', $artist->Biography) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Artist Statement</label>
                <textarea name="ArtistStatement" class="form-control" rows="3">{{ old('ArtistStatement', $artist->ArtistStatement) }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input name="IsProfilePublic" type="checkbox" value="1" class="form-check-input" id="isPublic" @checked($artist->IsProfilePublic) />
                    <label class="form-check-label" for="isPublic">Show my profile publicly</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Social Media Links</h2>
        <p class="text-muted small">Add up to 4 links (e.g. Facebook, Instagram, Website). Leave a row blank to omit it.</p>
        @foreach ($socialLinks as $i => $link)
            <div class="row g-2 mb-2">
                <input type="hidden" name="SocialLinks[{{ $i }}][id]" value="{{ $link['id'] }}" />
                <div class="col-4">
                    <input name="SocialLinks[{{ $i }}][platform]" class="form-control" placeholder="Platform (e.g. Instagram)" value="{{ $link['platform'] }}" />
                </div>
                <div class="col-8">
                    <input name="SocialLinks[{{ $i }}][url]" class="form-control" placeholder="https://..." value="{{ $link['url'] }}" />
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Save Profile</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('artists.show', $artist->Slug) }}" target="_blank">Preview Public Profile</a>
</form>

@endsection

@push('scripts')
<script src="{{ asset('js/location-cascade.js') }}"></script>
<script>
    document.querySelectorAll('[data-others-toggle]').forEach(function (cb) {
        var wrap = document.getElementById(cb.getAttribute('data-others-toggle'));
        function sync() { wrap.hidden = !cb.checked; if (!cb.checked) wrap.querySelector('input').value = ''; }
        cb.addEventListener('change', sync);
    });
</script>
@endpush
