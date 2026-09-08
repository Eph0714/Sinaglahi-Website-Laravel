@extends('layouts.admin')

@section('title', $photo ? 'Edit Photo' : 'Add Photo')

@section('content')

<a href="{{ route('admin.why-join.photos') }}" class="small">&larr; Back to Photo Gallery</a>
<h1 class="h3 mb-4 mt-1">{{ $photo ? 'Edit Photo' : 'Add Photo' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $photo ? route('admin.why-join.photos.update', $photo->Id) : route('admin.why-join.photos.store') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Photo</h2>
        @if ($photo?->ImagePath)
            <img src="{{ $photo->ImagePath }}" class="mb-2 rounded" style="max-height:200px;" />
        @endif
        <input name="Image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
    </div>

    <div class="form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $photo->Title ?? '') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Caption</label>
                <input name="Caption" class="form-control" value="{{ old('Caption', $photo->Caption ?? '') }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="2">{{ old('Description', $photo->Description ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input name="IsActive" type="checkbox" value="1" class="form-check-input" id="isActive" @checked(old('IsActive', $photo->IsActive ?? true)) />
                    <label class="form-check-label" for="isActive">Active</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input name="IsFeatured" type="checkbox" value="1" class="form-check-input" id="isFeatured" @checked(old('IsFeatured', $photo->IsFeatured ?? false)) />
                    <label class="form-check-label" for="isFeatured">Featured Photo</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $photo ? 'Save Changes' : 'Add Photo' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.why-join.photos') }}">Cancel</a>
</form>

@endsection
