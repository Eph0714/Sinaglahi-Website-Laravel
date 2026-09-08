@extends('layouts.admin')

@section('title', 'Edit Admin Account')

@section('content')

<h1 class="h3 mb-4">Edit Admin Account</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.users.update', $user->Id) }}" method="post" novalidate>
    @csrf

    <div class="form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Display Name</label>
                <input name="DisplayName" class="form-control" value="{{ old('DisplayName', $user->DisplayName) }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Admin Role</label>
                <select name="AdminRoleId" class="form-select" required>
                    <option value="">-- No Role --</option>
                    @foreach ($roleOptions as $r)
                        <option value="{{ $r->Id }}" @selected(old('AdminRoleId', $user->AdminRoleId) == $r->Id)>{{ $r->Name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Permissions</h2>
        <p class="text-muted small">
            Checked items shaded gold come from the assigned role. Check or uncheck any box to grant or
            revoke that specific permission for this admin, without changing their role.
        </p>
        @foreach ($permissions->groupBy('module') as $module => $group)
            <h3 class="h6 text-uppercase text-muted mt-3">{{ $module }}</h3>
            <div class="row">
                @foreach ($group as $perm)
                    <div class="col-md-4 col-lg-3 mb-2">
                        <div class="form-check {{ $perm->isGrantedByRole ? 'permission-from-role' : '' }}">
                            <input class="form-check-input" type="checkbox" name="grantedPermissionIds[]" value="{{ $perm->id }}"
                                   id="perm-{{ $perm->id }}" @checked($perm->isEffectivelyGranted) />
                            <label class="form-check-label" for="perm-{{ $perm->id }}">{{ $perm->label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Save Changes</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.users.index') }}">Cancel</a>
</form>

@endsection
