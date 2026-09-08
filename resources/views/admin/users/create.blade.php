@extends('layouts.admin')

@section('title', 'Add Admin Account')

@section('content')

<h1 class="h3 mb-4">Add Admin Account</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.users.store') }}" method="post" novalidate>
    @csrf

    <div class="form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input name="Email" type="email" class="form-control" autocomplete="username" value="{{ old('Email') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Display Name</label>
                <input name="DisplayName" class="form-control" placeholder="e.g. Juan Dela Cruz" value="{{ old('DisplayName') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input name="Password" type="password" class="form-control" autocomplete="new-password" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input name="Password_confirmation" type="password" class="form-control" autocomplete="new-password" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Admin Role</label>
                <select name="AdminRoleId" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    @foreach ($roleOptions as $r)
                        <option value="{{ $r->Id }}" @selected(old('AdminRoleId') == $r->Id)>{{ $r->Name }}</option>
                    @endforeach
                </select>
                <p class="text-muted small mt-1">You can fine-tune this admin's exact permissions after creating the account.</p>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Create Admin Account</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.users.index') }}">Cancel</a>
</form>

@endsection
