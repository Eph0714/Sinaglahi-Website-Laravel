@extends('layouts.admin')

@section('title', $artist->ArtistName)

@section('content')

@if (session('artistMessage'))
    <div class="alert alert-success">{{ session('artistMessage') }}</div>
@endif
@if (session('artistError'))
    <div class="alert alert-danger">{{ session('artistError') }}</div>
@endif

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <img src="{{ $artist->ProfilePhotoPath ?? placeholder_image(96, 96, $artist->ArtistName) }}"
             alt="{{ $artist->ArtistName }}" style="width:72px;height:72px;object-fit:cover;border-radius:50%;" />
        <div>
            <h1 class="h3 mb-1">{{ $artist->ArtistName }}</h1>
            <p class="text-muted mb-0">{{ $artist->FullName }} &middot; {{ $artist->user->Email ?? '' }}</p>
        </div>
    </div>
    <span class="status-badge status-{{ strtolower($artist->statusLabel()) }} fs-6">{{ $artist->statusLabel() }}</span>
</div>

<div class="form-section">
    <h2>Actions</h2>
    <div class="action-toolbar d-flex flex-wrap gap-2">
        @if (! $artist->IsVerified || (int) $artist->AccountStatus === \App\Models\Artist::STATUS_PENDING)
            <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#approveModal">Approve Artist</button>
            <form action="{{ route('admin.artists.reject', $artist->Id) }}" method="post" onsubmit="return confirm('Reject this artist registration?');">
                @csrf
                <button class="btn btn-outline-danger">Reject</button>
            </form>
        @endif
        @if ((int) $artist->AccountStatus !== \App\Models\Artist::STATUS_ACTIVE)
            <form action="{{ route('admin.artists.activate', $artist->Id) }}" method="post">
                @csrf
                <button class="btn btn-outline-brand">Activate</button>
            </form>
        @endif
        @if ((int) $artist->AccountStatus === \App\Models\Artist::STATUS_ACTIVE)
            <form action="{{ route('admin.artists.deactivate', $artist->Id) }}" method="post" onsubmit="return confirm('Deactivate this artist? They will immediately lose access.');">
                @csrf
                <button class="btn btn-outline-secondary">Deactivate</button>
            </form>
        @endif
        <form action="{{ route('admin.artists.toggle-featured', $artist->Id) }}" method="post">
            @csrf
            <button class="btn btn-outline-brand">{{ $artist->IsFeatured ? 'Remove from Featured' : 'Feature' }}</button>
        </form>
        <a class="btn btn-outline-brand" href="{{ route('admin.artists.edit', $artist->Id) }}">Edit</a>
        @if ($artist->isPubliclyVisible())
            <a class="btn btn-outline-brand" href="{{ route('artists.show', $artist->Slug) }}" target="_blank">View Public Profile</a>
        @endif
        <button type="button" class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#forceDeleteModal">Force Delete Artist</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="form-section review-block">
            <h2>Profile</h2>
            <dl class="row">
                <dt class="col-sm-4">Specialization</dt><dd class="col-sm-8">{{ $artist->Specialization ?? '—' }}</dd>
                <dt class="col-sm-4">Preferred Medium</dt><dd class="col-sm-8">{{ $artist->PreferredMedium ?? '—' }}</dd>
                <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $artist->Municipality ?? '—' }}, {{ $artist->Province ?? '—' }}</dd>
                <dt class="col-sm-4">Years Active</dt><dd class="col-sm-8">{{ $artist->YearsActive ?? '—' }}</dd>
                <dt class="col-sm-4">Verified</dt><dd class="col-sm-8">{{ $artist->IsVerified ? 'Yes ('.optional($artist->VerifiedDate)->format('M j, Y').')' : 'No' }}</dd>
                <dt class="col-sm-4">Public Profile</dt><dd class="col-sm-8">{{ $artist->IsProfilePublic ? 'Visible' : 'Hidden' }}</dd>
                <dt class="col-sm-4">Featured</dt><dd class="col-sm-8">{{ $artist->IsFeatured ? 'Yes' : 'No' }}</dd>
            </dl>
            @if ($artist->Biography)
                <h3 class="h6 text-uppercase text-muted mt-3">Biography</h3>
                <p class="text-muted">{{ $artist->Biography }}</p>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="mb-0">Artworks ({{ $artworks->count() }})</h2>
            </div>
            @if ($artworks->isEmpty())
                <p class="text-muted small">No artworks yet.</p>
            @else
                <div class="row g-2">
                    @foreach ($artworks as $art)
                        <div class="col-4">
                            <a href="{{ route('admin.artworks.show', $art->Id) }}">
                                <img src="{{ $art->ImagePath }}" class="w-100 rounded" style="aspect-ratio:1/1;object-fit:cover;" alt="{{ $art->Title }}" />
                            </a>
                            <div class="small mt-1">{{ $art->Title }}</div>
                            <span class="status-badge status-{{ strtolower($art->statusLabel()) }}">{{ $art->statusLabel() }}</span>
                            <div class="d-flex gap-1 mt-1">
                                <a class="btn btn-sm btn-outline-brand flex-fill" href="{{ route('admin.artworks.edit', $art->Id) }}">Edit</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="forceDeleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.artists.destroy', $artist->Id) }}" method="post" id="forceDeleteArtistForm">
        @csrf
        <div class="modal-header"><h5 class="modal-title text-danger">Force Delete Artist</h5></div>
        <div class="modal-body">
          <p class="fw-semibold">This action will permanently delete the artist profile, associated artworks, and uploaded files. This action cannot be undone. Do you want to continue?</p>
          <p class="text-muted small">Artist: <strong>{{ $artist->ArtistName }}</strong> &middot; {{ $artworks->count() }} artwork(s) will also be permanently deleted.</p>
          <label class="form-label">Type <strong>{{ $artist->ArtistName }}</strong> to confirm:</label>
          <input type="text" name="confirmName" id="confirmArtistName" class="form-control" autocomplete="off" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="confirmForceDeleteBtn" disabled>Force Delete Permanently</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.artists.approve', $artist->Id) }}" method="post">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Approve Artist</h5></div>
        <div class="modal-body">Are you sure you want to approve this artist? This will verify their account and set it to Active.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-brand">Yes, Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    var expectedArtistName = @json($artist->ArtistName);
    document.getElementById('confirmArtistName').addEventListener('input', function (e) {
        document.getElementById('confirmForceDeleteBtn').disabled = e.target.value !== expectedArtistName;
    });
    document.getElementById('forceDeleteArtistForm').addEventListener('submit', function () {
        document.getElementById('confirmForceDeleteBtn').disabled = true;
    });
</script>
@endpush
