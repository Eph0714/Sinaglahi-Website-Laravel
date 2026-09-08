@extends('layouts.admin')

@section('title', 'Menu Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Menu Management</h1>
    <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#addItemModal">Add Menu Item</button>
</div>

@if (session('menuMessage'))
    <div class="alert alert-success">{{ session('menuMessage') }}</div>
@endif

@foreach ($locations as $value => $label)
    @php $items = $itemsByLocation->get($value, collect())->sortBy('DisplayOrder'); @endphp
    <div class="form-section">
        <h2>{{ $label }}</h2>
        @if ($items->isEmpty())
            <p class="text-muted small">No items yet.</p>
        @else
            <div class="table-responsive">
                <table class="dash-table">
                    <thead><tr><th>Label</th><th>URL</th><th>Order</th><th>New Tab</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->Label }}</td>
                                <td class="small">{{ $item->Url }}</td>
                                <td>{{ $item->DisplayOrder }}</td>
                                <td>{{ $item->OpenInNewTab ? 'Yes' : 'No' }}</td>
                                <td><span class="status-badge {{ $item->IsActive ? 'status-published' : 'status-archived' }}">{{ $item->IsActive ? 'Active' : 'Inactive' }}</span></td>
                                <td class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#editItemModal-{{ $item->Id }}">Edit</button>
                                    <form action="{{ route('admin.navigation.toggle', $item->Id) }}" method="post">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">{{ $item->IsActive ? 'Disable' : 'Enable' }}</button>
                                    </form>
                                    <form action="{{ route('admin.navigation.destroy', $item->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record and associated data. This action cannot be undone. Are you sure you want to continue?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="editItemModal-{{ $item->Id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.navigation.update') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="Id" value="{{ $item->Id }}" />
                                            <div class="modal-header"><h5 class="modal-title">Edit Menu Item</h5></div>
                                            <div class="modal-body">
                                                <div class="mb-2"><label class="form-label">Label</label><input name="Label" class="form-control" value="{{ $item->Label }}" required maxlength="100" /></div>
                                                <div class="mb-2"><label class="form-label">URL</label><input name="Url" class="form-control" value="{{ $item->Url }}" required maxlength="500" /></div>
                                                <div class="mb-2">
                                                    <label class="form-label">Location</label>
                                                    <select name="Location" class="form-select">
                                                        @foreach ($locations as $lv => $ll)
                                                            <option value="{{ $lv }}" @selected($lv == $item->Location)>{{ $ll }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2"><label class="form-label">Display Order</label><input type="number" name="DisplayOrder" class="form-control" value="{{ $item->DisplayOrder }}" /></div>
                                                <div class="form-check"><input type="checkbox" name="OpenInNewTab" value="1" class="form-check-input" @checked($item->OpenInNewTab) /><label class="form-check-label">Open in new tab</label></div>
                                                <div class="form-check"><input type="checkbox" name="IsActive" value="1" class="form-check-input" @checked($item->IsActive) /><label class="form-check-label">Active</label></div>
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
    </div>
@endforeach

<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.navigation.store') }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Menu Item</h5></div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label">Label</label><input name="Label" class="form-control" required maxlength="100" /></div>
                    <div class="mb-2"><label class="form-label">URL</label><input name="Url" class="form-control" required maxlength="500" placeholder="/artists or https://..." /></div>
                    <div class="mb-2">
                        <label class="form-label">Location</label>
                        <select name="Location" class="form-select">
                            @foreach ($locations as $lv => $ll)
                                <option value="{{ $lv }}">{{ $ll }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label">Display Order</label><input type="number" name="DisplayOrder" class="form-control" value="0" /></div>
                    <div class="form-check"><input type="checkbox" name="OpenInNewTab" value="1" class="form-check-input" /><label class="form-check-label">Open in new tab</label></div>
                    <div class="form-check"><input type="checkbox" name="IsActive" value="1" class="form-check-input" checked /><label class="form-check-label">Active</label></div>
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
