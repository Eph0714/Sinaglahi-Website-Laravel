@extends('layouts.admin')

@section('title', 'Artwork Management')

@php $filters = ['Pending', 'Published', 'Featured', 'Rejected', 'Application']; @endphp

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Artwork Management</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.artworks.create') }}">Add Artwork</a>
</div>

@if (session('artworkMessage'))
    <div class="alert alert-success">{{ session('artworkMessage') }}</div>
@endif

<div class="filter-pills">
    <a class="filter-pill {{ ! $filter ? 'active' : '' }}" href="{{ route('admin.artworks.index') }}">All</a>
    @foreach ($filters as $f)
        <a class="filter-pill {{ $filter === $f ? 'active' : '' }}" href="{{ route('admin.artworks.index', ['filter' => $f]) }}">{{ $f }}</a>
    @endforeach
</div>

<form method="get" class="filter-bar row g-3 align-items-end">
    <input type="hidden" name="filter" value="{{ $filter }}" />
    <div class="col-md-6">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Title or artist name" />
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
    </div>
</form>

@if ($artworks->isEmpty())
    <div class="empty-state"><h3>No artworks found</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead>
                <tr><th></th><th>Title</th><th>Artist</th><th>Medium</th><th>Year</th><th>Status</th><th>Featured</th><th>Submitted</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($artworks as $a)
                    <tr>
                        <td><img class="thumb" src="{{ $a->ImagePath }}" alt="{{ $a->Title }}" /></td>
                        <td>{{ $a->Title }}</td>
                        <td>{{ $a->artist->ArtistName ?? '(unknown)' }}</td>
                        <td>{{ $a->Medium }}</td>
                        <td>{{ $a->Year }}</td>
                        <td><span class="status-badge status-{{ strtolower($a->statusLabel()) }}">{{ $a->statusLabel() }}</span></td>
                        <td>{{ $a->IsFeatured ? '★' : '—' }}</td>
                        <td>{{ $a->SubmittedAt->format('M j, Y') }}</td>
                        <td class="d-flex gap-1">
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.artworks.show', $a->Id) }}">View</a>
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.artworks.edit', $a->Id) }}">Edit</a>
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
                    <a class="page-link" href="{{ route('admin.artworks.index', array_filter(['page' => $p, 'search' => $search, 'filter' => $filter])) }}">{{ $p }}</a>
                </li>
            @endfor
        </ul></nav>
    @endif
@endif

@endsection
