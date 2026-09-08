@extends('layouts.admin')

@section('title', 'Admin Roles & Permissions')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Admin Roles &amp; Permissions</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.roles.create') }}">Add Role</a>
</div>

@if (session('roleMessage'))
    <div class="alert alert-success">{{ session('roleMessage') }}</div>
@endif
@if (session('roleError'))
    <div class="alert alert-danger">{{ session('roleError') }}</div>
@endif

@if ($roles->isEmpty())
    <div class="empty-state"><h3>No admin roles yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Role Name</th><th>Description</th><th>Permissions</th><th>Assigned Admins</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($roles as $r)
                    <tr>
                        <td>{{ $r->Name }}</td>
                        <td class="small text-muted">{{ $r->Description }}</td>
                        <td>{{ $r->permissions_count }}</td>
                        <td>{{ $r->users_count }}</td>
                        <td class="d-flex gap-1">
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.roles.edit', $r->Id) }}">Edit</a>
                            <form action="{{ route('admin.roles.destroy', $r->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record and associated data. This action cannot be undone. Are you sure you want to continue?');">
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
