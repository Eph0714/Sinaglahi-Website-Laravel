@extends('layouts.app')

@section('title', 'Application Status')

@section('content')

<section class="page-hero page-hero-compact">
    <div class="container text-center">
        <h1>Application Status</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="col-lg-7 mx-auto">
            <div class="status-welcome-banner mb-4 text-center">
                <h2 class="mb-1">{{ $applicantName }}</h2>
                <span class="status-badge-lg {{ \App\Services\ApplicationStatusPresentation::badgeClass($status) }}">{{ $statusLabel }}</span>
                <p class="mt-2 mb-0">{{ $statusDescription }}</p>
            </div>

            @if (count($timeline))
                <div class="status-timeline mb-4">
                    @foreach ($timeline as $step)
                        <div class="status-timeline-step is-{{ $step['state'] }}">
                            <div class="status-timeline-dot">{{ $step['state'] === 'done' ? '✓' : '' }}</div>
                            <div class="status-timeline-label">{{ $step['label'] }}</div>
                            @if (!$loop->last)
                                <div class="status-timeline-connector"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="status-result-details row form-section">
                <dt class="col-sm-5">Reference Number</dt><dd class="col-sm-7">{{ $referenceNumber }}</dd>
                <dt class="col-sm-5">Registered Mobile</dt><dd class="col-sm-7">{{ $maskedContactNumber }}</dd>
                <dt class="col-sm-5">Submitted</dt><dd class="col-sm-7">{{ optional($submittedAt)->format('F j, Y') }}</dd>
                <dt class="col-sm-5">Last Updated</dt><dd class="col-sm-7">{{ optional($lastUpdatedAt)->format('F j, Y') }}</dd>
                @if ($hasArtistAccount)
                    <dt class="col-sm-5">Artist Account</dt><dd class="col-sm-7">Created - you may now <a href="{{ route('account.login') }}">sign in</a>.</dd>
                @endif
            </div>

            @if ($applicantVisibleNotes->isNotEmpty())
                <div class="form-section">
                    <h3 class="h6">Notes from the Administration</h3>
                    <ul>
                        @foreach ($applicantVisibleNotes as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($history->isNotEmpty())
                <div class="form-section">
                    <h3 class="h6">History</h3>
                    <table class="status-history-table w-100">
                        <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            @foreach ($history as $h)
                                <tr>
                                    <td>{{ $h->ChangedAt->format('M j, Y') }}</td>
                                    <td>{{ \App\Services\ApplicationStatusPresentation::label((int) $h->Status) }}</td>
                                    <td>{{ $h->Remarks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($canEditForRevision)
                <form action="{{ route('join.edit-application') }}" method="post" class="text-center mt-3">
                    @csrf
                    <input type="hidden" name="referenceNumber" value="{{ $referenceNumberForEdit }}" />
                    <input type="hidden" name="contactNumber" value="{{ $contactNumberForEdit }}" />
                    <button type="submit" class="btn btn-primary-brand btn-lg">Edit Application</button>
                </form>
            @endif
        </div>
    </div>
</section>

@endsection
