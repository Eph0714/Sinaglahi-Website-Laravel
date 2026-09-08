@extends('layouts.app')

@section('title', 'Personal Information')

@php
    $genders = ['Male' => 0, 'Female' => 1, 'Non-binary' => 2, 'Prefer not to say' => 3, 'Other' => 4];
@endphp

@section('content')

<section class="section">
    <div class="container">
        <div class="join-steps mb-4">
            <div class="join-step join-step-current"><span class="join-step-dot">1</span> <span class="join-step-label">Personal Info</span></div>
            <div class="join-step"><span class="join-step-dot">2</span> <span class="join-step-label">Art Background</span></div>
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

            <form action="{{ route('join.personal-info.store') }}" method="post" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="form-section">
                    <h2>Profile Photo</h2>
                    <div class="text-center">
                        @if ($app->ProfilePhotoPath)
                            <img src="{{ route('join.personal-info-photo') }}" class="artist-avatar mb-2" alt="Current profile photo" />
                        @endif
                        <div class="col-sm-6 mx-auto">
                            <input name="ProfilePhoto" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
                            <p class="text-muted small mt-1 mb-0">Square photo recommended.</p>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Personal Information</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input name="FirstName" class="form-control" value="{{ old('FirstName', $app->FirstName) }}" required />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Middle Initial</label>
                            <input name="MiddleInitial" class="form-control" maxlength="10" value="{{ old('MiddleInitial', $app->MiddleInitial) }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input name="LastName" class="form-control" value="{{ old('LastName', $app->LastName) }}" required />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Extension Name</label>
                            <input name="ExtensionName" class="form-control" placeholder="Jr., Sr., III" value="{{ old('ExtensionName', $app->ExtensionName) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Preferred Name</label>
                            <input name="PreferredName" class="form-control" value="{{ old('PreferredName', $app->PreferredName) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Birth Date</label>
                            <input name="DateOfBirth" type="date" class="form-control" max="{{ now()->toDateString() }}" value="{{ old('DateOfBirth', optional($app->DateOfBirth)->format('Y-m-d')) }}" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gender</label>
                            <select name="Gender" class="form-select" required>
                                <option value="">-- Select --</option>
                                @foreach ($genders as $label => $value)
                                    <option value="{{ $value }}" @selected(old('Gender', $app->Gender) == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">If Other, please specify</label>
                            <input name="GenderOther" class="form-control" value="{{ old('GenderOther', $app->GenderOther) }}" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input name="Address" class="form-control" value="{{ old('Address', $app->Address) }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Province</label>
                            <select name="Province" class="form-select" data-province-select="Province" required>
                                <option value="">-- Select Province --</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}" @selected(old('Province', $app->Province) === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Municipality / City</label>
                            <div data-municipality-wrap="Province" data-field-name="Municipality" data-current-value="{{ old('Municipality', $app->Municipality) }}"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input name="ContactNumber" class="form-control" value="{{ old('ContactNumber', $app->ContactNumber) }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input name="Email" type="email" class="form-control" value="{{ old('Email', $app->Email) }}" required />
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Current Affiliation</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Current Art Organization</label>
                            <input name="CurrentArtOrganization" class="form-control" placeholder='Enter "None" if not applicable' value="{{ old('CurrentArtOrganization', $app->CurrentArtOrganization) }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">School or Institution (if applicable)</label>
                            <input name="School" class="form-control" value="{{ old('School', $app->School) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Occupation</label>
                            <input name="Occupation" class="form-control" value="{{ old('Occupation', $app->Occupation) }}" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-brand btn-lg">Continue</button>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('js/location-cascade.js') }}"></script>
@endpush
