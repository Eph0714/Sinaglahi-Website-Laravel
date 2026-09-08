@extends('layouts.admin')

@section('title', $testimonial ? 'Edit Testimonial' : 'Add Testimonial')

@section('content')

<h1 class="h3 mb-4">{{ $testimonial ? 'Edit Testimonial' : 'Add Testimonial' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $testimonial ? route('admin.testimonials.update', $testimonial->Id) : route('admin.testimonials.store') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Photo</h2>
        @if ($testimonial?->ImagePath)
            <img src="{{ $testimonial->ImagePath }}" class="mb-2 rounded-circle" style="width:80px;height:80px;object-fit:cover;" />
        @endif
        <input name="Image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
    </div>

    <div class="form-section">
        <h2>Content</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="Name" class="form-control" value="{{ old('Name', $testimonial->Name ?? '') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Position</label>
                <input name="Position" class="form-control" value="{{ old('Position', $testimonial->Position ?? '') }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Quote</label>
                <textarea name="Quote" class="form-control" rows="3" required>{{ old('Quote', $testimonial->Quote ?? '') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input name="Date" type="date" class="form-control" value="{{ old('Date', optional($testimonial?->Date)->format('Y-m-d')) }}" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Display Order</label>
                <input name="DisplayOrder" type="number" class="form-control" value="{{ old('DisplayOrder', $testimonial->DisplayOrder ?? 0) }}" />
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input name="IsPublished" type="checkbox" value="1" class="form-check-input" id="isPublished" @checked(old('IsPublished', $testimonial->IsPublished ?? true)) />
                    <label class="form-check-label" for="isPublished">Published</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $testimonial ? 'Save Changes' : 'Add Testimonial' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.testimonials.index') }}">Cancel</a>
</form>

@endsection
