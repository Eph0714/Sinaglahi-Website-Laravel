@extends('layouts.admin')

@section('title', 'User Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">User Management</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-brand" href="{{ route('admin.users.transfer-super-admin') }}">Transfer Super Admin</a>
        <a class="btn btn-primary-brand" href="{{ route('admin.users.create') }}">Add Admin</a>
    </div>
</div>

@if (session('userMessage'))
    <div class="alert alert-success">{{ session('userMessage') }}</div>
@endif
@if (session('userError'))
    <div class="alert alert-danger">{{ session('userError') }}</div>
@endif

<div class="table-responsive">
    <table class="dash-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Last Login</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach ($users as $u)
                <tr>
                    <td>{{ $u->displayName ?? '—' }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if ($u->isSuperAdmin)
                            <span class="status-badge status-published">Super Admin</span>
                        @else
                            <span>{{ $u->adminRoleName ?? '(no role assigned)' }}</span>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $u->isActive ? 'status-published' : 'status-archived' }}">{{ $u->isActive ? 'Active' : 'Disabled' }}</span></td>
                    <td class="small">{{ optional($u->createdAt)->format('M j, Y') }}</td>
                    <td class="small">{{ $u->lastLoginAt ? $u->lastLoginAt->format('M j, Y g:i A') : 'Never' }}</td>
                    <td class="row-actions">
                        @if (! $u->isSuperAdmin)
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.users.edit', $u->id) }}">Edit</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.reset-password', $u->id) }}">Reset Password</a>
                            <form action="{{ route('admin.users.toggle-active', $u->id) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $u->isActive ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record and associated data. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        @else
                            <span class="text-muted small">Protected account</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
