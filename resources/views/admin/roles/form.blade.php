@extends('layouts.admin')

@section('title', $role ? 'Edit Admin Role' : 'Add Admin Role')

@section('content')

<h1 class="h3 mb-4">{{ $role ? 'Edit Admin Role' : 'Add Admin Role' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $role ? route('admin.roles.update', $role->Id) : route('admin.roles.store') }}" method="post" novalidate>
    @csrf

    <div class="form-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="Name" class="form-control" value="{{ old('Name', $role->Name ?? '') }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Description</label>
                <input name="Description" class="form-control" value="{{ old('Description', $role->Description ?? '') }}" />
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Permissions</h2>
        <p class="text-muted small">
            Choose exactly which actions admins assigned to this role may perform. Super-Admin-only
            permissions (Users &amp; Roles management) are never offered here.
        </p>
        @foreach ($permissionGroups as $module => $group)
            <h3 class="h6 text-uppercase text-muted mt-3">{{ $module }}</h3>
            <div class="row">
                @foreach ($group as $perm)
                    <div class="col-md-4 col-lg-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="SelectedPermissionIds[]" value="{{ $perm->Id }}"
                                   id="rp-{{ $perm->Id }}" @checked(in_array($perm->Id, $selectedPermissionIds)) />
                            <label class="form-check-label" for="rp-{{ $perm->Id }}">{{ $perm->Label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Save Role</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.roles.index') }}">Cancel</a>
</form>

@endsection
