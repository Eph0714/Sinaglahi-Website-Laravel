@extends('layouts.admin')

@section('title', 'Application: '.$app->FullName)

@php
    $genders = [0 => 'Male', 1 => 'Female', 2 => 'Non-binary', 3 => 'Prefer not to say', 4 => 'Other'];
    $applicantTypes = [0 => 'I am an artist and want to join the group', 1 => 'I am an aspiring artist', 2 => 'I am interested in learning about art', 3 => 'I am a supporter or art enthusiast', 4 => 'I am only interested in viewing the gallery at this time', 5 => 'Other'];
    $referralSources = [0 => 'Facebook', 1 => 'Instagram', 2 => 'Website', 3 => 'Friend or family member', 4 => 'Current Sinaglahi member', 5 => 'Art exhibition', 6 => 'School', 7 => 'Community event', 8 => 'Other'];
@endphp

@section('content')

@if (session('applicationMessage'))
    <div class="alert alert-success">{{ session('applicationMessage') }}</div>
@endif
@if (session('applicationError'))
    <div class="alert alert-danger">{{ session('applicationError') }}</div>
@endif

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        @if ($app->ProfilePhotoPath)
            <img src="{{ route('admin.applications.profile-photo', $app->Id) }}" alt="{{ $app->FullName }}" style="width:72px;height:72px;object-fit:cover;border-radius:50%;" />
        @endif
        <div>
            <h1 class="h3 mb-1">{{ $app->FullName }}</h1>
            <p class="text-muted mb-0">Reference: <strong>{{ $app->ReferenceNumber }}</strong> &middot; Submitted {{ optional($app->SubmittedAt)->format('M j, Y g:i A') }}</p>
        </div>
    </div>
    <span class="status-badge status-{{ $app->statusCssSlug() }} fs-6">{{ $app->statusLabel() }}</span>
</div>

<div class="form-section">
    <h2>Review Actions</h2>
    <div class="action-toolbar d-flex flex-wrap gap-2">
        @if (in_array((int) $app->Status, [\App\Models\MembershipApplication::STATUS_PENDING_REVIEW, \App\Models\MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED], true))
            <form action="{{ route('admin.applications.mark-under-review', $app->Id) }}" method="post">
                @csrf
                <button class="btn btn-outline-brand">Mark Under Review</button>
            </form>
        @endif
        @if (! in_array((int) $app->Status, [\App\Models\MembershipApplication::STATUS_APPROVED, \App\Models\MembershipApplication::STATUS_REJECTED, \App\Models\MembershipApplication::STATUS_ARCHIVED], true))
            <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#approveModal">Approve Application</button>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject Application</button>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#requestInfoModal">Request Additional Information</button>
        @endif
        @if ((int) $app->Status !== \App\Models\MembershipApplication::STATUS_ARCHIVED)
            <form action="{{ route('admin.applications.archive', $app->Id) }}" method="post" onsubmit="return confirm('Archive this application?');">
                @csrf
                <button class="btn btn-outline-secondary">Archive</button>
            </form>
        @endif
        @if ((int) $app->Status === \App\Models\MembershipApplication::STATUS_APPROVED && ! $alreadyConverted)
            <button class="btn btn-join" data-bs-toggle="modal" data-bs-target="#convertModal">Create Artist Account</button>
        @endif
        @if ($alreadyConverted)
            <a class="btn btn-outline-brand" href="{{ route('admin.artists.show', $app->ConvertedArtistId) }}">View Created Artist Account</a>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="form-section review-block">
            <h2>Applicant Information</h2>
            <dl class="row">
                <dt class="col-sm-3">Full Name</dt><dd class="col-sm-9">{{ $app->FullName }} {{ $app->PreferredName ? "({$app->PreferredName})" : '' }}</dd>
                <dt class="col-sm-3">Birth Date</dt><dd class="col-sm-9">{{ $app->DateOfBirth ? $app->DateOfBirth->format('F j, Y') : '—' }}</dd>
                <dt class="col-sm-3">Age / Gender</dt><dd class="col-sm-9">{{ $app->Age }} &middot; {{ $genders[(int) $app->Gender] ?? '' }} {{ (int) $app->Gender === 4 ? "({$app->GenderOther})" : '' }}</dd>
                <dt class="col-sm-3">Address</dt><dd class="col-sm-9">{{ $app->Address }}, {{ $app->Municipality }}, {{ $app->Province }}</dd>
                <dt class="col-sm-3">Contact</dt><dd class="col-sm-9">{{ $app->ContactNumber }} &middot; {{ $app->Email }}</dd>
            </dl>

            <h3 class="h6 text-uppercase text-muted mt-4">Affiliation</h3>
            <dl class="row">
                <dt class="col-sm-3">Current Art Organization</dt><dd class="col-sm-9">{{ $app->CurrentArtOrganization }}</dd>
                <dt class="col-sm-3">School</dt><dd class="col-sm-9">{{ $app->School ?? '—' }}</dd>
                <dt class="col-sm-3">Occupation</dt><dd class="col-sm-9">{{ $app->Occupation ?? '—' }}</dd>
            </dl>

            <h3 class="h6 text-uppercase text-muted mt-4">Art Background</h3>
            <dl class="row">
                <dt class="col-sm-3">Classification</dt><dd class="col-sm-9">{{ $applicantTypes[(int) $app->ApplicantType] ?? '' }} {{ (int) $app->ApplicantType === 5 ? "({$app->ApplicantTypeOther})" : '' }}</dd>
                <dt class="col-sm-3">Mediums</dt><dd class="col-sm-9">{{ $app->ArtMediums }} {{ $app->ArtMediumsOther ? "({$app->ArtMediumsOther})" : '' }}</dd>
                <dt class="col-sm-3">Years Practicing</dt><dd class="col-sm-9">{{ $app->YearsPracticingArt ?? '—' }}</dd>
                <dt class="col-sm-3">Art Type</dt><dd class="col-sm-9">{{ $app->ArtType ?? '—' }}</dd>
                <dt class="col-sm-3">Exhibition Experience</dt><dd class="col-sm-9">{{ $app->HasExhibitionExperience ? ($app->ExhibitionExperience ?: 'Yes') : 'No' }}</dd>
                <dt class="col-sm-3">Other Organizations</dt><dd class="col-sm-9">{{ $app->HasOtherOrganizationExperience ? ($app->OtherOrganizationExperience ?: 'Yes') : 'No' }}</dd>
                <dt class="col-sm-3">Sells/Displays Artwork</dt><dd class="col-sm-9">{{ $app->SellsOrDisplaysArtwork ? 'Yes' : 'No' }}</dd>
                <dt class="col-sm-3">Portfolio</dt><dd class="col-sm-9">{{ $app->PortfolioUrl ?: '—' }}</dd>
            </dl>

            <h3 class="h6 text-uppercase text-muted mt-4">Significant Questions</h3>
            <dl class="row">
                <dt class="col-sm-3">Why join?</dt><dd class="col-sm-9">{{ $app->WhyJoin }}</dd>
                <dt class="col-sm-3">How they heard about us</dt><dd class="col-sm-9">{{ $referralSources[(int) $app->HowLearnedAboutSinaglahi] ?? '' }} {{ $app->HowLearnedAboutSinaglahiDetails ? "— {$app->HowLearnedAboutSinaglahiDetails}" : '' }}</dd>
                <dt class="col-sm-3">Experience / goals</dt><dd class="col-sm-9">{{ $app->ArtExperience }}</dd>
                <dt class="col-sm-3">Contribution / expectations</dt><dd class="col-sm-9">{{ $app->ContributionOrExpectations }}</dd>
            </dl>

            <h3 class="h6 text-uppercase text-muted mt-4">Submitted Artworks ({{ $artworks->count() }})</h3>
            <div class="row g-3">
                @foreach ($artworks as $art)
                    <div class="col-6 col-md-4 col-lg-3">
                        <img src="{{ route('admin.applications.artwork-image', $art->Id) }}" alt="{{ $art->Title }}" class="w-100 rounded" style="aspect-ratio:1/1;object-fit:cover;" />
                        <div class="small mt-1 fw-semibold">{{ $art->Title }}</div>
                        <div class="small text-muted">{{ $art->Medium }} {{ $art->Year ? "&middot; {$art->Year}" : '' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h2>Notes</h2>
            <form action="{{ route('admin.applications.add-note', $app->Id) }}" method="post" class="mb-3">
                @csrf
                <textarea name="note" class="form-control mb-2" rows="2" placeholder="Add an internal note..."></textarea>
                <button type="submit" class="btn btn-outline-brand btn-sm">Add Note</button>
            </form>
            @if ($notes->isEmpty())
                <p class="text-muted small">No notes yet.</p>
            @else
                @foreach ($notes as $note)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="small">{{ $note->Note }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $note->CreatedAt->format('M j, Y g:i A') }}
                            {{ $note->IsApplicantVisible ? ' · visible to applicant' : ' · internal only' }}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="form-section mt-3">
            <h2>Status History</h2>
            @if ($statusHistory->isEmpty())
                <p class="text-muted small">No status changes recorded yet.</p>
            @else
                @foreach ($statusHistory as $h)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="small"><strong>{{ \App\Services\ApplicationStatusPresentation::label((int) $h->Status) }}</strong>{{ $h->Remarks ? " — {$h->Remarks}" : '' }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $h->ChangedAt->format('M j, Y g:i A') }}
                            {{ $h->ChangedByUserId === null ? ' · by applicant' : ' · by admin' }}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.applications.approve', $app->Id) }}" method="post">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Approve Application</h5></div>
        <div class="modal-body">Are you sure you want to approve this membership application?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-brand">Yes, Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.applications.reject', $app->Id) }}" method="post">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject Application</h5></div>
        <div class="modal-body">
          <label class="form-label">Reason (required)</label>
          <textarea name="reason" class="form-control" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-danger">Reject Application</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="requestInfoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.applications.request-info', $app->Id) }}" method="post">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Request Additional Information</h5></div>
        <div class="modal-body">
          <label class="form-label">Message to applicant</label>
          <textarea name="note" class="form-control" rows="3"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-secondary">Send Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="convertModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.applications.convert-to-artist', $app->Id) }}" method="post">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Create Artist Account</h5></div>
        <div class="modal-body">
          <p class="text-muted small">This creates a login account and artist profile for {{ $app->FullName }}, and copies over their submitted artworks.</p>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="activateImmediately" value="1" id="activateImmediately" checked>
            <label class="form-check-label" for="activateImmediately">Verify and activate the artist account immediately</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="publishArtworks" value="1" id="publishArtworks">
            <label class="form-check-label" for="publishArtworks">Publish their submitted artworks immediately (otherwise they go to Pending Review)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-join">Create Artist Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
