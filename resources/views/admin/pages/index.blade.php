@extends('layouts.admin')

@section('title', 'Pages Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Pages Management</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.pages.create') }}">Add Page</a>
</div>

@if (session('pageMessage'))
    <div class="alert alert-success">{{ session('pageMessage') }}</div>
@endif

@if ($pages->isEmpty())
    <div class="empty-state"><h3>No custom pages yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Title</th><th>Slug</th><th>Order</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($pages as $p)
                    <tr>
                        <td>{{ $p->Title }}</td>
                        <td class="small">/pages/{{ $p->Slug }}</td>
                        <td>{{ $p->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $p->IsPublished ? 'status-published' : 'status-draft' }}">{{ $p->IsPublished ? 'Published' : 'Draft' }}</span></td>
                        <td class="small">{{ ($p->UpdatedAt ?? $p->CreatedAt)->format('M j, Y') }}</td>
                        <td class="row-actions">
                            @if ($p->IsPublished)
                                <a class="btn btn-sm btn-outline-secondary" href="/pages/{{ $p->Slug }}" target="_blank">View</a>
                            @endif
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.pages.edit', $p->Id) }}">Edit</a>
                            <form action="{{ route('admin.pages.toggle', $p->Id) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $p->IsPublished ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <form action="{{ route('admin.pages.destroy', $p->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record and associated data. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
