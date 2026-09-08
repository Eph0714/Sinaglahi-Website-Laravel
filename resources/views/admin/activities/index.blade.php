@extends('layouts.admin')

@section('title', 'Manage Activities')

@php $filters = ['Published', 'Draft', 'Featured']; @endphp

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Manage Activities</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.activities.create') }}">Add Activity</a>
</div>

@if (session('activityMessage'))
    <div class="alert alert-success">{{ session('activityMessage') }}</div>
@endif

<div class="filter-pills">
    <a class="filter-pill {{ ! $filter ? 'active' : '' }}" href="{{ route('admin.activities.index') }}">All</a>
    @foreach ($filters as $f)
        <a class="filter-pill {{ $filter === $f ? 'active' : '' }}" href="{{ route('admin.activities.index', ['filter' => $f]) }}">{{ $f }}</a>
    @endforeach
</div>

<form method="get" class="filter-bar row g-3 align-items-end">
    <input type="hidden" name="filter" value="{{ $filter }}" />
    <div class="col-md-6">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Title or location" />
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
    </div>
</form>

@if ($activities->isEmpty())
    <div class="empty-state"><h3>No activities found</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th></th><th>Title</th><th>Date</th><th>Category</th><th>Location</th><th>Photos</th><th>Homepage Featured</th><th>Activity Status</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($activities as $a)
                    <tr>
                        <td><img class="thumb" src="{{ $a->CoverPhotoPath ?? placeholder_image(80, 80, $a->Title) }}" alt="{{ $a->Title }}" /></td>
                        <td>{{ $a->Title }}</td>
                        <td>{{ $a->ActivityDate->format('M j, Y') }}</td>
                        <td>{{ $a->category->Name ?? '' }}</td>
                        <td>{{ $a->Location }}</td>
                        <td>{{ $a->PhotoCount }}</td>
                        <td>{{ $a->IsFeatured ? '★' : '—' }}</td>
                        <td><span class="activity-status-badge activity-status-{{ strtolower($a->statusLabel()) }}">{{ $a->statusLabel() }}</span></td>
                        <td><span class="status-badge {{ $a->IsPublished ? 'status-published' : 'status-draft' }}">{{ $a->IsPublished ? 'Published' : 'Draft' }}</span></td>
                        <td class="row-actions">
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.activities.edit', $a->Id) }}">Edit</a>
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.activities.manage-photos', $a->Id) }}">Photos</a>
                            @if ($a->IsPublished)
                                <form action="{{ route('admin.activities.unpublish', $a->Id) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Unpublish</button>
                                </form>
                            @else
                                <form action="{{ route('admin.activities.publish', $a->Id) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Publish</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.activities.toggle-featured', $a->Id) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-brand">{{ $a->IsFeatured ? 'Unfeature' : 'Feature' }}</button>
                            </form>
                            <form action="{{ route('admin.activities.destroy', $a->Id) }}" method="post" onsubmit="return confirm('Force Delete Activity\n\n&quot;{{ $a->Title }}&quot; contains {{ $a->PhotoCount }} photo(s). This action will permanently delete this record and its associated gallery records/files. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
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
                    <a class="page-link" href="{{ route('admin.activities.index', array_filter(['page' => $p, 'search' => $search, 'filter' => $filter])) }}">{{ $p }}</a>
                </li>
            @endfor
        </ul></nav>
    @endif
@endif

@endsection
