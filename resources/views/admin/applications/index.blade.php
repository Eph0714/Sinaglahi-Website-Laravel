@extends('layouts.admin')

@section('title', 'Membership Applications')

@php
    $statuses = [1 => 'Submitted', 2 => 'PendingReview', 3 => 'UnderReview', 4 => 'AdditionalInfoRequired', 5 => 'Approved', 6 => 'Rejected', 7 => 'Withdrawn', 8 => 'Archived'];
@endphp

@section('content')

<h1 class="h3 mb-4">Membership Applications</h1>

<div class="filter-pills">
    <a class="filter-pill {{ ! $status ? 'active' : '' }}" href="{{ route('admin.applications.index') }}">All</a>
    @foreach ($statuses as $value => $label)
        <a class="filter-pill {{ (string) $status === (string) $value ? 'active' : '' }}" href="{{ route('admin.applications.index', ['status' => $value]) }}">{{ $label }}</a>
    @endforeach
</div>

<form method="get" class="filter-bar row g-3 align-items-end">
    <input type="hidden" name="status" value="{{ $status }}" />
    <div class="col-md-4">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Name, email, contact, or reference #" />
    </div>
    <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" name="dateFrom" class="form-control" value="{{ $dateFrom }}" />
    </div>
    <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" name="dateTo" class="form-control" value="{{ $dateTo }}" />
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
    </div>
</form>

@if ($applications->isEmpty())
    <div class="empty-state"><h3>No applications found</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Applicant</th><th>Email</th><th>Contact</th><th>Organization</th>
                    <th>Artworks</th><th>Submitted</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $a)
                    <tr>
                        <td>{{ $a->FullName }}<div class="text-muted small">{{ $a->ReferenceNumber }}</div></td>
                        <td>{{ $a->Email }}</td>
                        <td>{{ $a->ContactNumber }}</td>
                        <td>{{ $a->CurrentArtOrganization }}</td>
                        <td>{{ $a->ArtworkCount }}</td>
                        <td>{{ optional($a->SubmittedAt)->format('M j, Y') }}</td>
                        <td><span class="status-badge status-{{ $a->statusCssSlug() }}">{{ $a->statusLabel() }}</span></td>
                        <td><a class="btn btn-sm btn-outline-brand" href="{{ route('admin.applications.show', $a->Id) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($totalPages > 1)
        <nav class="mt-4"><ul class="pagination pagination-brand">
            @for ($p = 1; $p <= $totalPages; $p++)
                <li class="page-item {{ $p == $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('admin.applications.index', array_filter(['page' => $p, 'search' => $search, 'status' => $status])) }}">{{ $p }}</a>
                </li>
            @endfor
        </ul></nav>
    @endif
@endif

@endsection
