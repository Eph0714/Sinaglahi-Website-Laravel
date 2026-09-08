@extends('layouts.admin')

@section('title', 'Artist Management')

@php $filters = ['Pending', 'Verified', 'Active', 'Inactive', 'Rejected']; @endphp

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Artist Management</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.artists.create') }}">Add Artist</a>
</div>

@if (session('artistMessage'))
    <div class="alert alert-success">{{ session('artistMessage') }}</div>
@endif

<div class="filter-pills">
    <a class="filter-pill {{ ! $filter ? 'active' : '' }}" href="{{ route('admin.artists.index') }}">All</a>
    @foreach ($filters as $f)
        <a class="filter-pill {{ $filter === $f ? 'active' : '' }}" href="{{ route('admin.artists.index', ['filter' => $f]) }}">{{ $f }}</a>
    @endforeach
</div>

<form method="get" class="filter-bar row g-3 align-items-end">
    <input type="hidden" name="filter" value="{{ $filter }}" />
    <div class="col-md-6">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Artist name, email, or username" />
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
    </div>
</form>

@if ($artists->isEmpty())
    <div class="empty-state"><h3>No artists found</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead>
                <tr><th></th><th>Artist Name</th><th>Email</th><th>Username</th><th>Verified</th><th>Status</th><th>Registered</th><th>Artworks</th><th>Source</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($artists as $a)
                    <tr>
                        <td><img class="thumb" style="border-radius:50%;" src="{{ $a->ProfilePhotoPath ?? placeholder_image(80, 80, $a->ArtistName) }}" alt="{{ $a->ArtistName }}" /></td>
                        <td>{{ $a->ArtistName }}</td>
                        <td>{{ $a->user->Email ?? '' }}</td>
                        <td>{{ $a->user->UserName ?? '' }}</td>
                        <td>{{ $a->IsVerified ? '✓' : '—' }}</td>
                        <td><span class="status-badge status-{{ strtolower($a->statusLabel()) }}">{{ $a->statusLabel() }}</span></td>
                        <td>{{ $a->CreatedAt->format('M j, Y') }}</td>
                        <td>{{ $a->ArtworkCount }}</td>
                        <td>{{ $a->SourceApplicationId ? 'Join Application' : 'Self-registered' }}</td>
                        <td class="d-flex gap-1">
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.artists.show', $a->Id) }}">View</a>
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.artists.edit', $a->Id) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($totalPages > 1)
        <nav class="mt-4"><ul class="pagination pagination-brand">
            @for ($p = 1; $p <= $totalPages; $p++)
                <li class="page-item {{ $p == $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('admin.artists.index', array_filter(['page' => $p, 'search' => $search, 'filter' => $filter])) }}">{{ $p }}</a>
                </li>
            @endfor
        </ul></nav>
    @endif
@endif

@endsection
