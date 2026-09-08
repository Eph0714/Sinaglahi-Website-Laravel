@extends('layouts.admin')

@section('title', 'Website Settings')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Website Settings</h1>
    <a class="btn btn-outline-brand" href="{{ route('admin.settings.social-links') }}">Manage Social Links &rarr;</a>
</div>

@if (session('settingsMessage'))
    <div class="alert alert-success">{{ session('settingsMessage') }}</div>
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

<form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>General Information</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Website Name</label>
                <input name="WebsiteName" class="form-control" value="{{ old('WebsiteName', $settings->WebsiteName) }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Organization Name</label>
                <input name="OrganizationName" class="form-control" value="{{ old('OrganizationName', $settings->OrganizationName) }}" required />
            </div>
            <div class="col-md-4">
                <label class="form-label">Short Name</label>
                <input name="ShortName" class="form-control" value="{{ old('ShortName', $settings->ShortName) }}" />
            </div>
            <div class="col-md-8">
                <label class="form-label">Tagline</label>
                <input name="Tagline" class="form-control" value="{{ old('Tagline', $settings->Tagline) }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="2">{{ old('Description', $settings->Description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Logo &amp; Favicon</h2>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <label class="form-label d-block">Logo (light)</label>
                @if ($settings->LogoPath)
                    <img src="{{ $settings->LogoPath }}" class="img-fluid mb-2" style="max-height:60px;" />
                @endif
                <input name="Logo" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control form-control-sm" />
                @if ($settings->LogoPath)
                    <form action="{{ route('admin.settings.delete-logo') }}" method="post" class="mt-1">
                        @csrf
                        <input type="hidden" name="field" value="Logo" />
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                @endif
            </div>
            <div class="col-md-3 text-center">
                <label class="form-label d-block">Logo (dark-mode)</label>
                @if ($settings->LogoDarkPath)
                    <img src="{{ $settings->LogoDarkPath }}" class="img-fluid mb-2" style="max-height:60px;" />
                @endif
                <input name="LogoDark" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control form-control-sm" />
                @if ($settings->LogoDarkPath)
                    <form action="{{ route('admin.settings.delete-logo') }}" method="post" class="mt-1">
                        @csrf
                        <input type="hidden" name="field" value="LogoDark" />
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                @endif
            </div>
            <div class="col-md-3 text-center">
                <label class="form-label d-block">Favicon</label>
                @if ($settings->FaviconPath)
                    <img src="{{ $settings->FaviconPath }}" class="mb-2" style="max-height:40px;" />
                @endif
                <input name="Favicon" type="file" accept=".png,.jpg,.jpeg,.webp" class="form-control form-control-sm" />
                @if ($settings->FaviconPath)
                    <form action="{{ route('admin.settings.delete-logo') }}" method="post" class="mt-1">
                        @csrf
                        <input type="hidden" name="field" value="Favicon" />
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                @endif
            </div>
            <div class="col-md-3 text-center">
                <label class="form-label d-block">Mobile Logo</label>
                @if ($settings->MobileLogoPath)
                    <img src="{{ $settings->MobileLogoPath }}" class="img-fluid mb-2" style="max-height:60px;" />
                @endif
                <input name="MobileLogo" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control form-control-sm" />
                @if ($settings->MobileLogoPath)
                    <form action="{{ route('admin.settings.delete-logo') }}" method="post" class="mt-1">
                        @csrf
                        <input type="hidden" name="field" value="MobileLogo" />
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Contact Information</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Contact Email</label>
                <input name="ContactEmail" type="email" class="form-control" value="{{ old('ContactEmail', $settings->ContactEmail) }}" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Contact Number</label>
                <input name="ContactNumber" class="form-control" value="{{ old('ContactNumber', $settings->ContactNumber) }}" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Mobile Number</label>
                <input name="MobileNumber" class="form-control" value="{{ old('MobileNumber', $settings->MobileNumber) }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <input name="Address" class="form-control" value="{{ old('Address', $settings->Address) }}" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Province</label>
                <select name="Province" class="form-select" data-province-select="Province">
                    <option value="">-- Select Province --</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}" @selected($settings->Province === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Municipality / City</label>
                <div data-municipality-wrap="Province" data-field-name="Municipality" data-current-value="{{ $settings->Municipality }}"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Country</label>
                <input name="Country" class="form-control" value="{{ old('Country', $settings->Country) }}" />
            </div>
            <div class="col-md-2">
                <label class="form-label">Zip Code</label>
                <input name="ZipCode" class="form-control" value="{{ old('ZipCode', $settings->ZipCode) }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Google Maps Embed URL</label>
                <input name="GoogleMapsUrl" class="form-control" placeholder="https://www.google.com/maps/embed?..." value="{{ old('GoogleMapsUrl', $settings->GoogleMapsUrl) }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Office Hours</label>
                <input name="OfficeHours" class="form-control" placeholder="e.g. Mon-Fri, 9am-5pm" value="{{ old('OfficeHours', $settings->OfficeHours) }}" />
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Footer</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Footer Text</label>
                <textarea name="FooterText" class="form-control" rows="2">{{ old('FooterText', $settings->FooterText) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Copyright Text</label>
                <input name="CopyrightText" class="form-control" value="{{ old('CopyrightText', $settings->CopyrightText) }}" />
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Default SEO</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">SEO Title</label>
                <input name="SeoTitle" class="form-control" value="{{ old('SeoTitle', $settings->SeoTitle) }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">SEO Keywords</label>
                <input name="SeoKeywords" class="form-control" placeholder="comma, separated, keywords" value="{{ old('SeoKeywords', $settings->SeoKeywords) }}" />
            </div>
            <div class="col-12">
                <label class="form-label">SEO Meta Description</label>
                <textarea name="SeoMetaDescription" class="form-control" rows="2">{{ old('SeoMetaDescription', $settings->SeoMetaDescription) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-block">Default Social Sharing Image</label>
                @if ($settings->SeoDefaultOgImagePath)
                    <img src="{{ $settings->SeoDefaultOgImagePath }}" class="img-fluid mb-2" style="max-height:100px;" />
                @endif
                <input name="SeoDefaultOgImage" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control form-control-sm" />
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Save Settings</button>
</form>

@endsection

@push('scripts')
<script src="{{ asset('js/location-cascade.js') }}"></script>
@endpush
