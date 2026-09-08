@extends('layouts.admin')

@section('title', $artist ? 'Edit Artist: '.$artist->ArtistName : 'Add Artist')

@section('content')

<h1 class="h3 mb-4">{{ $artist ? 'Edit Artist: '.$artist->ArtistName : 'Add Artist' }}</h1>
@unless ($artist)
    <p class="text-muted">Creates a new login account and artist profile together. The artist can sign in immediately with the email and password set below.</p>
@endunless

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $artist ? route('admin.artists.update', $artist->Id) : route('admin.artists.store') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    @unless ($artist)
        <div class="form-section">
            <h2>Account</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input name="Email" type="email" class="form-control" autocomplete="off" value="{{ old('Email') }}" required />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Password</label>
                    <input name="Password" type="password" class="form-control" autocomplete="new-password" required />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Confirm Password</label>
                    <input name="Password_confirmation" type="password" class="form-control" autocomplete="new-password" required />
                </div>
            </div>
        </div>
    @endunless

    <div class="form-section">
        <h2>Photo</h2>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <label class="form-label d-block">Profile Photo</label>
                @if ($artist?->ProfilePhotoPath)
                    <img class="artist-avatar mb-2" style="max-width:160px; border-radius:50%;" src="{{ $artist->ProfilePhotoPath }}" alt="Current photo" />
                @endif
                <input name="ProfilePhoto" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
                <p class="text-muted small mt-1 mb-0">JPG, PNG, or WebP. Up to 10 MB.</p>
                @if ($artist?->ProfilePhotoPath)
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
                <input name="ArtistName" class="form-control" value="{{ old('ArtistName', $artist->ArtistName ?? '') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input name="FullName" class="form-control" value="{{ old('FullName', $artist->FullName ?? '') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Province</label>
                <select name="Province" class="form-select" data-province-select="Province">
                    <option value="">-- Select Province --</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}" @selected(($artist->Province ?? null) === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Municipality / City</label>
                <div data-municipality-wrap="Province" data-field-name="Municipality" data-current-value="{{ $artist->Municipality ?? '' }}"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Birthday</label>
                <input name="DateOfBirth" type="date" class="form-control" max="{{ now()->toDateString() }}" value="{{ optional($artist?->DateOfBirth)->format('Y-m-d') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Years Active</label>
                <input name="YearsActive" type="number" class="form-control" value="{{ old('YearsActive', $artist->YearsActive ?? '') }}" />
            </div>

            <div class="col-md-6">
                <label class="form-label">Art Specialization</label>
                <p class="text-muted small mb-1">Check all that apply. If none fit, check "Others" and specify.</p>
                @foreach ($specializations as $s)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Specializations[]" value="{{ $s }}" id="cspec-{{ \Illuminate\Support\Str::slug($s) }}" @checked(in_array($s, $specChecked)) />
                        <label class="form-check-label" for="cspec-{{ \Illuminate\Support\Str::slug($s) }}">{{ $s }}</label>
                    </div>
                @endforeach
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cSpecOthersCheck" data-others-toggle="cSpecializationOtherWrap" @checked($specOther) />
                    <label class="form-check-label" for="cSpecOthersCheck">Others</label>
                </div>
                <div id="cSpecializationOtherWrap" class="mt-1" @if(!$specOther) hidden @endif>
                    <input name="SpecializationOther" class="form-control" placeholder="Please specify" value="{{ $specOther }}" />
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Preferred Medium</label>
                <p class="text-muted small mb-1">Check all that apply. If none fit, check "Others" and specify.</p>
                @foreach ($mediums as $m)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Mediums[]" value="{{ $m }}" id="cmed-{{ \Illuminate\Support\Str::slug($m) }}" @checked(in_array($m, $medChecked)) />
                        <label class="form-check-label" for="cmed-{{ \Illuminate\Support\Str::slug($m) }}">{{ $m }}</label>
                    </div>
                @endforeach
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cMedOthersCheck" data-others-toggle="cMediumOtherWrap" @checked($medOther) />
                    <label class="form-check-label" for="cMedOthersCheck">Others</label>
                </div>
                <div id="cMediumOtherWrap" class="mt-1" @if(!$medOther) hidden @endif>
                    <input name="MediumOther" class="form-control" placeholder="Please specify" value="{{ $medOther }}" />
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Biography</label>
                <textarea name="Biography" class="form-control" rows="3">{{ old('Biography', $artist->Biography ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Artist Statement</label>
                <textarea name="ArtistStatement" class="form-control" rows="3">{{ old('ArtistStatement', $artist->ArtistStatement ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Contact</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Contact Phone</label>
                <input name="ContactPhone" class="form-control" value="{{ old('ContactPhone', $user->PhoneNumber ?? '') }}" />
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Social Media Links</h2>
        <p class="text-muted small">Add up to 4 links (e.g. Facebook, Instagram, Website). Leave a row blank to omit it.</p>
        @foreach ($socialLinks as $i => $link)
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <input name="SocialLinks[{{ $i }}][platform]" class="form-control" placeholder="Platform (e.g. Instagram)" value="{{ $link['platform'] }}" />
                </div>
                <div class="col-8">
                    <input name="SocialLinks[{{ $i }}][url]" class="form-control" placeholder="https://..." value="{{ $link['url'] }}" />
                </div>
            </div>
        @endforeach
    </div>

    <div class="form-section">
        <h2>Status</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Account Status</label>
                <select name="AccountStatus" class="form-select">
                    @foreach (['Pending' => 0, 'Active' => 1, 'Inactive' => 2, 'Rejected' => 3] as $label => $value)
                        <option value="{{ $value }}" @selected((int) ($artist->AccountStatus ?? 0) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input name="IsVerified" type="checkbox" value="1" class="form-check-input" id="isVerified" @checked($artist->IsVerified ?? false) />
                    <label class="form-check-label" for="isVerified">Verified</label>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input name="IsProfilePublic" type="checkbox" value="1" class="form-check-input" id="isPublic" @checked($artist->IsProfilePublic ?? true) />
                    <label class="form-check-label" for="isPublic">Show profile publicly</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input name="IsFeatured" type="checkbox" value="1" class="form-check-input" id="isFeatured" @checked($artist->IsFeatured ?? false) />
                    <label class="form-check-label" for="isFeatured">Featured</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $artist ? 'Save Changes' : 'Create Artist' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.artists.index') }}">Cancel</a>
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
