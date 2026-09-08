@extends('layouts.admin')

@section('title', $activity ? 'Edit Activity' : 'Add Activity')

@section('content')

<h1 class="h3 mb-4">{{ $activity ? 'Edit Activity' : 'Add Activity' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Cover Photo</h2>
        @if ($activity?->CoverPhotoPath)
            <img src="{{ $activity->CoverPhotoPath }}" alt="Current cover photo" class="mb-2 rounded" style="max-height:220px;" />
        @endif
        <input name="CoverPhoto" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
        <p class="text-muted small mt-1">You can also set the cover photo later from the gallery in "Manage Photos".</p>
    </div>

    <div class="form-section">
        <h2>Activity Information</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $activity->Title ?? '') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="CategoryId" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach ($categoryOptions as $c)
                        <option value="{{ $c->Id }}" @selected(old('CategoryId', $activity->CategoryId ?? null) == $c->Id)>{{ $c->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Activity Date</label>
                <input name="ActivityDate" type="date" class="form-control" value="{{ old('ActivityDate', optional($activity?->ActivityDate)->format('Y-m-d')) }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Location</label>
                <input name="Location" class="form-control" value="{{ old('Location', $activity->Location ?? '') }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="5" required>{{ old('Description', $activity->Description ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Facebook Post/Page URL</label>
                <input name="FacebookUrl" class="form-control" placeholder="https://www.facebook.com/..." value="{{ old('FacebookUrl', $activity->FacebookUrl ?? '') }}" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Activity Status</label>
                <select name="Status" class="form-select">
                    @foreach (['Upcoming' => 0, 'Ongoing' => 1, 'Completed' => 2] as $label => $value)
                        <option value="{{ $value }}" @selected((int) old('Status', $activity->Status ?? 0) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input name="IsFeatured" type="checkbox" value="1" class="form-check-input" id="isFeatured" @checked(old('IsFeatured', $activity->IsFeatured ?? false)) />
                    <label class="form-check-label" for="isFeatured">Homepage Featured Activity</label>
                </div>
                <p class="text-muted small mb-0">Homepage Featured + Published activities appear in the homepage Group Activities section.</p>
            </div>
            <div class="col-md-2">
                <label class="form-label">Featured Order</label>
                <input name="FeaturedOrder" type="number" class="form-control" value="{{ old('FeaturedOrder', $activity->FeaturedOrder ?? 0) }}" />
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input name="IsPublished" type="checkbox" value="1" class="form-check-input" id="isPublished" @checked(old('IsPublished', $activity->IsPublished ?? false)) />
                    <label class="form-check-label" for="isPublished">Published</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Save Activity</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.activities.index') }}">Cancel</a>
</form>

@endsection
