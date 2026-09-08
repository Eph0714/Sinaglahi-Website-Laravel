@extends('layouts.admin')

@section('title', $artwork->Title)

@section('content')

@if (session('artworkMessage'))
    <div class="alert alert-success">{{ session('artworkMessage') }}</div>
@endif
@if (session('artworkError'))
    <div class="alert alert-danger">{{ session('artworkError') }}</div>
@endif

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">{{ $artwork->Title }}</h1>
        <p class="text-muted mb-0">by {{ $artwork->artist->ArtistName ?? '' }} &middot; {{ $artwork->Medium }} &middot; {{ $artwork->Year }}</p>
    </div>
    <span class="status-badge status-{{ strtolower($artwork->statusLabel()) }} fs-6">{{ $artwork->statusLabel() }}</span>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <img src="{{ $artwork->ImagePath }}" class="w-100 rounded" alt="{{ $artwork->Title }}" />
    </div>
    <div class="col-lg-7">
        <div class="form-section">
            <h2>Actions</h2>
            <div class="action-toolbar mb-2 d-flex flex-wrap gap-2">
                @if ((int) $artwork->Status === \App\Models\Artwork::STATUS_PENDING_REVIEW)
                    <form action="{{ route('admin.artworks.approve', $artwork->Id) }}" method="post">
                        @csrf
                        <button class="btn btn-primary-brand">Approve</button>
                    </form>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                @endif
                @if (in_array((int) $artwork->Status, [\App\Models\Artwork::STATUS_APPROVED, \App\Models\Artwork::STATUS_PENDING_REVIEW], true))
                    <form action="{{ route('admin.artworks.publish', $artwork->Id) }}" method="post">
                        @csrf
                        <button class="btn btn-outline-brand">Publish</button>
                    </form>
                @endif
                @if ((int) $artwork->Status === \App\Models\Artwork::STATUS_PUBLISHED)
                    <form action="{{ route('admin.artworks.unpublish', $artwork->Id) }}" method="post" onsubmit="return confirm('Unpublish this artwork?');">
                        @csrf
                        <button class="btn btn-outline-secondary">Unpublish</button>
                    </form>
                @endif
                <form action="{{ route('admin.artworks.toggle-featured', $artwork->Id) }}" method="post">
                    @csrf
                    <button class="btn btn-outline-brand">{{ $artwork->IsFeatured ? 'Unfeature' : 'Feature' }}</button>
                </form>
                <a class="btn btn-outline-brand" href="{{ route('admin.artworks.edit', $artwork->Id) }}">Edit</a>
                @if ((int) $artwork->Status === \App\Models\Artwork::STATUS_PUBLISHED)
                    <a class="btn btn-outline-secondary" href="{{ route('artworks.show', $artwork->Slug) }}" target="_blank">View Public Page</a>
                @endif
                <button type="button" class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#forceDeleteArtworkModal">Force Delete</button>
            </div>
        </div>

        <div class="form-section review-block">
            <h2>Details</h2>
            <dl class="row">
                <dt class="col-sm-4">Artist</dt><dd class="col-sm-8"><a href="{{ route('admin.artists.show', $artwork->ArtistId) }}">{{ $artwork->artist->ArtistName ?? '' }}</a></dd>
                <dt class="col-sm-4">Size</dt><dd class="col-sm-8">{{ $artwork->Size }}</dd>
                <dt class="col-sm-4">Category</dt><dd class="col-sm-8">{{ $artwork->category->Name ?? '—' }}</dd>
                <dt class="col-sm-4">Price</dt><dd class="col-sm-8">{{ $artwork->Price ? '₱'.number_format($artwork->Price, 2) : '—' }}</dd>
                <dt class="col-sm-4">Submitted</dt><dd class="col-sm-8">{{ $artwork->SubmittedAt->format('M j, Y g:i A') }}</dd>
            </dl>
            <h3 class="h6 text-uppercase text-muted mt-3">Description</h3>
            <p class="text-muted">{{ $artwork->Description }}</p>
        </div>
    </div>
</div>

<div class="modal fade" id="forceDeleteArtworkModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.artworks.destroy', $artwork->Id) }}" method="post" id="forceDeleteArtworkForm">
        @csrf
        <div class="modal-header"><h5 class="modal-title text-danger">Force Delete Artwork</h5></div>
        <div class="modal-body">
          <p class="fw-semibold">This action will permanently delete the artwork and its associated files. This action cannot be undone.</p>
          <p class="text-muted small">Artwork: <strong>{{ $artwork->Title }}</strong> &middot; Artist: <strong>{{ $artwork->artist->ArtistName ?? '' }}</strong></p>
          <label class="form-label">Type <strong>{{ $artwork->Title }}</strong> to confirm:</label>
          <input type="text" name="confirmTitle" id="confirmArtworkTitle" class="form-control" autocomplete="off" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="confirmForceDeleteArtworkBtn" disabled>Force Delete Permanently</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.artworks.reject', $artwork->Id) }}" method="post">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject Artwork</h5></div>
        <div class="modal-body">
          <label class="form-label">Reason (optional, internal)</label>
          <textarea name="reason" class="form-control" rows="3"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-danger">Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    var expectedArtworkTitle = @json($artwork->Title);
    document.getElementById('confirmArtworkTitle').addEventListener('input', function (e) {
        document.getElementById('confirmForceDeleteArtworkBtn').disabled = e.target.value !== expectedArtworkTitle;
    });
    document.getElementById('forceDeleteArtworkForm').addEventListener('submit', function () {
        document.getElementById('confirmForceDeleteArtworkBtn').disabled = true;
    });
</script>
@endpush
