@extends('layouts.admin')

@section('title', 'Reset Password')

@section('content')

<h1 class="h3 mb-4">Reset Password</h1>
<p class="text-muted">For: <strong>{{ $email }}</strong></p>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.users.reset-password.store', $userId) }}" method="post" novalidate class="col-lg-6">
    @csrf

    <div class="form-section">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input name="NewPassword" type="password" class="form-control" autocomplete="new-password" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input name="NewPassword_confirmation" type="password" class="form-control" autocomplete="new-password" required />
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Reset Password</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.users.index') }}">Cancel</a>
</form>

@endsection
