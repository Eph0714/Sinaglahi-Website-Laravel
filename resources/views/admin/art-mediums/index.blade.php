@extends('layouts.admin')

@section('title', 'Art Mediums')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Art Mediums</h1>
        <p class="text-muted small mb-0">Manage the standardized Medium choices artists select from during Artwork Enrollment.</p>
    </div>
    <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#addMediumModal">Add Medium</button>
</div>

@if (session('mediumMessage'))
    <div class="alert alert-success">{{ session('mediumMessage') }}</div>
@endif
@if (session('mediumError'))
    <div class="alert alert-danger">{{ session('mediumError') }}</div>
@endif

@if ($mediums->isEmpty())
    <div class="empty-state"><h3>No mediums yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Order</th><th>Name</th><th>Status</th><th>Used By</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($mediums as $m)
                    <tr>
                        <td>
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.art-mediums.move', $m->Id) }}" method="post" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="up" />
                                    <button class="btn btn-sm btn-settings" title="Move up"><i class="bi bi-arrow-up"></i></button>
                                </form>
                                <form action="{{ route('admin.art-mediums.move', $m->Id) }}" method="post" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="down" />
                                    <button class="btn btn-sm btn-settings" title="Move down"><i class="bi bi-arrow-down"></i></button>
                                </form>
                            </div>
                        </td>
                        <td>
                            {{ $m->Name }}
                            @if ($m->IsOtherOption)
                                <span class="status-badge status-featured ms-1">Required</span>
                            @endif
                        </td>
                        <td><span class="status-badge {{ $m->IsActive ? 'status-published' : 'status-archived' }}">{{ $m->IsActive ? 'Active' : 'Inactive' }}</span></td>
                        <td class="small text-muted">{{ $m->UsageCount == 0 ? 'Not used yet' : "{$m->UsageCount} artwork(s)" }}</td>
                        <td class="row-actions">
                            <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editMediumModal-{{ $m->Id }}">Edit</button>
                            @if (! $m->IsOtherOption)
                                <form action="{{ route('admin.art-mediums.toggle', $m->Id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-settings">{{ $m->IsActive ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                @if ($m->UsageCount == 0)
                                    <form action="{{ route('admin.art-mediums.destroy', $m->Id) }}" method="post" class="d-inline" onsubmit="return confirm('Permanently delete this medium? This cannot be undone.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>

                    <div class="modal fade" id="editMediumModal-{{ $m->Id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.art-mediums.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="Id" value="{{ $m->Id }}" />
                                    <div class="modal-header"><h5 class="modal-title">Edit Medium</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2"><label class="form-label">Name</label><input name="Name" class="form-control" value="{{ $m->Name }}" required maxlength="100" /></div>
                                        @if (! $m->IsOtherOption)
                                            <div class="form-check"><input type="checkbox" name="IsActive" value="1" class="form-check-input" @checked($m->IsActive) /><label class="form-check-label">Active</label></div>
                                        @else
                                            <p class="text-muted small mb-0">This is the required "Other" option and always stays active.</p>
                                        @endif
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

<div class="modal fade" id="addMediumModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.art-mediums.store') }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Medium</h5></div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label">Name</label><input name="Name" class="form-control" required maxlength="100" placeholder="e.g. Egg Tempera" /></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-brand">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
