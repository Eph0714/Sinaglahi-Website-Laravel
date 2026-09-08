@extends('layouts.admin')

@section('title', $artwork ? 'Edit Artwork: '.$artwork->Title : 'Add Artwork')

@section('content')

<h1 class="h3 mb-4">{{ $artwork ? 'Edit Artwork: '.$artwork->Title : 'Add Artwork' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $artwork ? route('admin.artworks.update', $artwork->Id) : route('admin.artworks.store') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Artwork Photo</h2>
        @if ($artwork?->ImagePath)
            <img src="{{ $artwork->ImagePath }}" alt="Current artwork image" class="mb-2 rounded" style="max-height:220px;" />
        @endif
        <input name="Image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
    </div>

    <div class="form-section">
        <h2>Artwork Information</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Artist</label>
                <select name="ArtistId" class="form-select" required>
                    <option value="">-- Select Artist --</option>
                    @foreach ($artistOptions as $ar)
                        <option value="{{ $ar->Id }}" @selected(old('ArtistId', $artistId ?? $artwork->ArtistId ?? null) == $ar->Id)>{{ $ar->ArtistName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $artwork->Title ?? '') }}" required />
            </div>
            <div class="col-md-4">
                <label class="form-label">Year</label>
                <input name="Year" type="number" class="form-control" value="{{ old('Year', $artwork->Year ?? date('Y')) }}" required />
            </div>
            <div class="col-md-4">
                <label class="form-label">Medium</label>
                <select name="MediumId" class="form-select" id="mediumSelect" required>
                    <option value="">-- Select Medium --</option>
                    @foreach ($mediumOptions as $m)
                        <option value="{{ $m->Id }}" data-other="{{ $m->IsOtherOption ? 'true' : 'false' }}" @selected(old('MediumId', $effectiveMediumId ?? null) == $m->Id)>{{ $m->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4" id="customMediumWrap" hidden>
                <label class="form-label">Specify Medium</label>
                <input name="CustomMedium" class="form-control" value="{{ old('CustomMedium', $artwork->CustomMedium ?? '') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Size</label>
                <input name="Size" class="form-control" placeholder="e.g. 18 x 24 in" value="{{ old('Size', $artwork->Size ?? '') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="CategoryId" class="form-select">
                    <option value="">-- None --</option>
                    @foreach ($categoryOptions as $c)
                        <option value="{{ $c->Id }}" @selected(old('CategoryId', $artwork->CategoryId ?? null) == $c->Id)>{{ $c->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Price (optional)</label>
                <input name="Price" type="number" step="0.01" class="form-control" value="{{ old('Price', $artwork->Price ?? '') }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="4" required>{{ old('Description', $artwork->Description ?? '') }}</textarea>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input name="IsAvailable" type="checkbox" value="1" class="form-check-input" id="isAvailable" @checked(old('IsAvailable', $artwork->IsAvailable ?? true)) />
                    <label class="form-check-label" for="isAvailable">Available for inquiry</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input name="IsFeatured" type="checkbox" value="1" class="form-check-input" id="isFeatured" @checked(old('IsFeatured', $artwork->IsFeatured ?? false)) />
                    <label class="form-check-label" for="isFeatured">Featured</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="Status" class="form-select">
                    @foreach (['Draft' => 0, 'Pending Review' => 1, 'Approved' => 2, 'Rejected' => 3, 'Published' => 4, 'Archived' => 5] as $label => $value)
                        <option value="{{ $value }}" @selected((int) old('Status', $artwork->Status ?? 1) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $artwork ? 'Save Changes' : 'Create Artwork' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.artworks.index') }}">Cancel</a>
</form>

@endsection

@push('scripts')
<script>
    (function () {
        var select = document.getElementById('mediumSelect');
        var wrap = document.getElementById('customMediumWrap');
        if (!select || !wrap) return;
        function sync() {
            var opt = select.options[select.selectedIndex];
            wrap.hidden = !(opt && opt.getAttribute('data-other') === 'true');
        }
        select.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
