@extends('layouts.admin')

@section('title', 'Transfer Super Admin')

@section('content')

<h1 class="h3 mb-4">Transfer Super Admin</h1>

<div class="alert alert-warning">
    This permanently transfers the Super Admin role to another admin account. You will become a
    regular Admin with no assigned role afterward. This action requires your current password and
    cannot be undone from here - only the new Super Admin could transfer it back.
</div>

@if ($candidates->isEmpty())
    <div class="empty-state"><h3>No eligible Admin accounts</h3><p>Create an Admin account first before transferring ownership.</p></div>
@else
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.transfer-super-admin.store') }}" method="post" novalidate class="col-lg-6">
        @csrf

        <div class="form-section">
            <div class="mb-3">
                <label class="form-label">New Super Admin</label>
                <select name="TargetUserId" class="form-select" required>
                    <option value="">-- Select Admin --</option>
                    @foreach ($candidates as $c)
                        <option value="{{ $c->Id }}" @selected(old('TargetUserId') == $c->Id)>{{ $c->DisplayName ?? $c->Email }} ({{ $c->Email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Current Password</label>
                <input name="CurrentPassword" type="password" class="form-control" required />
            </div>
        </div>

        <button type="submit" class="btn btn-outline-danger btn-lg" onclick="return confirm('This will transfer Super Admin ownership and cannot be undone from this account. Continue?');">
            Transfer Super Admin
        </button>
        <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.users.index') }}">Cancel</a>
    </form>
@endif

@endsection
