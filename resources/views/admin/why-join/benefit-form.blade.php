@extends('layouts.admin')

@section('title', $benefit ? 'Edit Benefit Card' : 'Add Benefit Card')

@section('content')

<a href="{{ route('admin.why-join.benefits') }}" class="small">&larr; Back to Benefit Cards</a>
<h1 class="h3 mb-4 mt-1">{{ $benefit ? 'Edit Benefit Card' : 'Add Benefit Card' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $benefit ? route('admin.why-join.benefits.update', $benefit->Id) : route('admin.why-join.benefits.store') }}" method="post" novalidate>
    @csrf

    <div class="form-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Icon (emoji or Bootstrap Icon class)</label>
                <input name="Icon" class="form-control" value="{{ old('Icon', $benefit->Icon ?? '🎨') }}" required maxlength="20" />
            </div>
            <div class="col-md-9">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $benefit->Title ?? '') }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3" required>{{ old('Description', $benefit->Description ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input name="IsActive" type="checkbox" value="1" class="form-check-input" id="isActive" @checked(old('IsActive', $benefit->IsActive ?? true)) />
                    <label class="form-check-label" for="isActive">Active</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $benefit ? 'Save Changes' : 'Add Benefit Card' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.why-join.benefits') }}">Cancel</a>
</form>

@endsection
