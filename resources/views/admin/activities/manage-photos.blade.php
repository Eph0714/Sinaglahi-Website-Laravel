@extends('layouts.admin')

@section('title', 'Manage Photos')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">Manage Photos</h1>
        <p class="text-muted mb-0">{{ $activity->Title }}</p>
    </div>
    <a class="btn btn-outline-brand" href="{{ route('admin.activities.edit', $activity->Id) }}">&larr; Back to Activity Details</a>
</div>

@if (session('activityMessage'))
    <div class="alert alert-success">{{ session('activityMessage') }}</div>
@endif

<div class="form-section">
    <h2>Upload Photos</h2>
    <p class="text-muted small">Drag and drop multiple images here, or click to browse. JPG, PNG, or WebP, up to 10 MB each.</p>

    <div class="dropzone" id="photoDropzone">
        <p class="mb-1">Drag and drop photos here, or click to choose files</p>
        <p class="small mb-0" id="selectedCount"></p>
    </div>
    <input type="file" id="photoFileInput" accept=".jpg,.jpeg,.png,.webp" multiple class="d-none" />

    <div class="progress mt-3 d-none" id="uploadProgressWrap" style="height: 22px;">
        <div class="progress-bar bg-brand" id="uploadProgressBar" style="width: 0%;">0%</div>
    </div>

    <button type="button" id="startUploadBtn" class="btn btn-primary-brand mt-3 d-none">Upload Selected Photos</button>
</div>

<div class="form-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Gallery ({{ $photos->count() }} photos)</h2>
        <div class="d-flex gap-2">
            <button type="button" id="saveOrderBtn" class="btn btn-outline-brand btn-sm d-none">Save Order</button>
            <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger btn-sm" disabled>Delete Selected</button>
        </div>
    </div>

    @if ($photos->isEmpty())
        <p class="text-muted small">No photos uploaded yet.</p>
    @else
        <p class="text-muted small">Drag photos to reorder. Click a photo's star to set it as the cover image.</p>
        <div class="photo-manage-grid" id="photoGrid">
            @foreach ($photos as $photo)
                <div class="photo-manage-tile {{ $photo->IsHidden ? 'is-hidden' : '' }}" draggable="true" data-photo-id="{{ $photo->Id }}">
                    <div class="photo-manage-select">
                        <input type="checkbox" class="photo-select-checkbox" value="{{ $photo->Id }}" />
                    </div>
                    <button type="button" class="photo-manage-cover-btn {{ $photo->IsCover ? 'is-cover' : '' }}" data-photo-id="{{ $photo->Id }}" title="Set as Featured Photo">★</button>
                    @if ($photo->IsCover)
                        <span class="badge photo-manage-featured-badge">Featured</span>
                    @endif
                    <img src="{{ $photo->ThumbnailPath ?? $photo->FilePath }}" alt="{{ $photo->Caption ?? 'Activity photo' }}" class="photo-manage-img" data-photo-id="{{ $photo->Id }}" />
                    <label class="btn btn-sm btn-outline-brand w-100 mt-1 photo-replace-label">
                        Replace Photo
                        <input type="file" class="d-none photo-replace-input" data-photo-id="{{ $photo->Id }}" accept=".jpg,.jpeg,.png,.webp" />
                    </label>
                    <input type="text" class="form-control form-control-sm photo-caption-input mt-1" data-photo-id="{{ $photo->Id }}"
                           value="{{ $photo->Caption }}" placeholder="Add a caption..." />
                    <textarea class="form-control form-control-sm photo-description-input mt-1" data-photo-id="{{ $photo->Id }}"
                              placeholder="Add a description..." rows="2">{{ $photo->Description }}</textarea>
                    @if ($photo->IsHidden)
                        <form action="{{ route('admin.activities.unhide-photo', [$activity->Id, $photo->Id]) }}" method="post" class="mt-1">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success w-100">Unhide (reversible)</button>
                        </form>
                    @else
                        <form action="{{ route('admin.activities.hide-photo', [$activity->Id, $photo->Id]) }}" method="post" class="mt-1">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Hide (reversible)</button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-1 photo-delete-btn" data-photo-id="{{ $photo->Id }}">Delete</button>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('scripts')
<!-- Hidden form solely to render a fresh antiforgery token for this page's AJAX calls. -->
<form id="tokenForm" style="display:none;">@csrf</form>
<script>
    window.__activityId = {{ $activity->Id }};
    window.__antiforgeryToken = document.querySelector('#tokenForm input[name="_token"]').value;
</script>
<script src="{{ asset('js/activity-photos.js') }}"></script>
@endpush
