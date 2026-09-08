@extends('layouts.admin')

@section('title', $banner ? 'Edit Banner' : 'Add Banner')

@section('content')

<h1 class="h3 mb-4">{{ $banner ? 'Edit Banner' : 'Add Banner' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $banner ? route('admin.banners.update', $banner->Id) : route('admin.banners.store') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Banner Image</h2>
        @if ($banner?->ImagePath)
            <img src="{{ $banner->ImagePath }}" alt="Current banner image" class="mb-2 rounded" style="max-height:220px;" />
        @endif
        <input name="Image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
    </div>

    <div class="form-section">
        <h2>Content</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $banner->Title ?? '') }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Subtitle</label>
                <input name="Subtitle" class="form-control" value="{{ old('Subtitle', $banner->Subtitle ?? '') }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="2">{{ old('Description', $banner->Description ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Button Text</label>
                <input name="ButtonText" class="form-control" value="{{ old('ButtonText', $banner->ButtonText ?? '') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Button URL</label>
                <input name="ButtonUrl" class="form-control" value="{{ old('ButtonUrl', $banner->ButtonUrl ?? '') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Second Button Text</label>
                <input name="SecondButtonText" class="form-control" value="{{ old('SecondButtonText', $banner->SecondButtonText ?? '') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Second Button URL</label>
                <input name="SecondButtonUrl" class="form-control" value="{{ old('SecondButtonUrl', $banner->SecondButtonUrl ?? '') }}" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Display Order</label>
                <input name="DisplayOrder" type="number" class="form-control" value="{{ old('DisplayOrder', $banner->DisplayOrder ?? 0) }}" />
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input name="IsEnabled" type="checkbox" value="1" class="form-check-input" id="isEnabled" @checked(old('IsEnabled', $banner->IsEnabled ?? true)) />
                    <label class="form-check-label" for="isEnabled">Enabled</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input name="IsFeatured" type="checkbox" value="1" class="form-check-input" id="isFeatured" @checked(old('IsFeatured', $banner->IsFeatured ?? false)) />
                    <label class="form-check-label" for="isFeatured">Featured</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $banner ? 'Save Changes' : 'Create Banner' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.banners.index') }}">Cancel</a>
</form>

@endsection
