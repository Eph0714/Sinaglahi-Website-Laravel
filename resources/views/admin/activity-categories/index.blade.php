@extends('layouts.admin')

@section('title', 'Activity Categories')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Activity Categories</h1>
    <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
</div>

@if (session('categoryMessage'))
    <div class="alert alert-success">{{ session('categoryMessage') }}</div>
@endif
@if (session('categoryError'))
    <div class="alert alert-danger">{{ session('categoryError') }}</div>
@endif

@if ($categories->isEmpty())
    <div class="empty-state"><h3>No categories yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Name</th><th>Description</th><th>Activities</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr>
                        <td>{{ $c->Name }}</td>
                        <td class="small text-muted">{{ $c->Description }}</td>
                        <td>{{ $c->ActivityCount }}</td>
                        <td>{{ $c->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $c->IsActive ? 'status-published' : 'status-archived' }}">{{ $c->IsActive ? 'Active' : 'Inactive' }}</span></td>
                        <td class="row-actions">
                            <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#editCategoryModal-{{ $c->Id }}">Edit</button>
                            <form action="{{ route('admin.activity-categories.toggle', $c->Id) }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $c->IsActive ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form action="{{ route('admin.activity-categories.destroy', $c->Id) }}" method="post" class="d-inline" onsubmit="return confirm('This action will permanently delete this record and associated data. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editCategoryModal-{{ $c->Id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.activity-categories.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="Id" value="{{ $c->Id }}" />
                                    <div class="modal-header"><h5 class="modal-title">Edit Category</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Name</label>
                                            <input name="Name" class="form-control" value="{{ $c->Name }}" required maxlength="100" />
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Description</label>
                                            <textarea name="Description" class="form-control" rows="2" maxlength="500">{{ $c->Description }}</textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Display Order</label>
                                            <input type="number" name="DisplayOrder" class="form-control" value="{{ $c->DisplayOrder }}" />
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="IsActive" value="1" class="form-check-input" @checked($c->IsActive) />
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary-brand">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.activity-categories.store') }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Category</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input name="Name" class="form-control" required maxlength="100" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="DisplayOrder" class="form-control" value="0" />
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="IsActive" value="1" class="form-check-input" checked />
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-brand">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
