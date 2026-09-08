@extends('layouts.admin')

@section('title', 'Social Media Settings')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Social Media Settings</h1>
    <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#addSocialModal">Add Social Link</button>
</div>

@if (session('settingsMessage'))
    <div class="alert alert-success">{{ session('settingsMessage') }}</div>
@endif

@if ($links->isEmpty())
    <div class="empty-state"><h3>No social links yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Platform</th><th>URL</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($links as $link)
                    <tr>
                        <td>{{ $link->Platform }}</td>
                        <td class="small"><a href="{{ $link->Url }}" target="_blank">{{ $link->Url }}</a></td>
                        <td>{{ $link->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $link->IsActive ? 'status-published' : 'status-archived' }}">{{ $link->IsActive ? 'Active' : 'Inactive' }}</span></td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#editSocialModal-{{ $link->Id }}">Edit</button>
                            <form action="{{ route('admin.settings.social-links.destroy', $link->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editSocialModal-{{ $link->Id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.settings.social-links.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="Id" value="{{ $link->Id }}" />
                                    <div class="modal-header"><h5 class="modal-title">Edit Social Link</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2"><label class="form-label">Platform</label><input name="Platform" class="form-control" value="{{ $link->Platform }}" required maxlength="50" /></div>
                                        <div class="mb-2"><label class="form-label">URL</label><input name="Url" class="form-control" value="{{ $link->Url }}" required maxlength="500" /></div>
                                        <div class="mb-2"><label class="form-label">Display Order</label><input type="number" name="DisplayOrder" class="form-control" value="{{ $link->DisplayOrder }}" /></div>
                                        <div class="form-check"><input type="checkbox" name="IsActive" value="1" class="form-check-input" @checked($link->IsActive) /><label class="form-check-label">Active</label></div>
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

<div class="modal fade" id="addSocialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.social-links.store') }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Social Link</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Platform</label>
                        <select name="Platform" class="form-select">
                            <option>Facebook</option><option>Instagram</option><option>YouTube</option>
                            <option>TikTok</option><option>Messenger</option><option>Other</option>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label">URL</label><input name="Url" class="form-control" required maxlength="500" placeholder="https://..." /></div>
                    <div class="mb-2"><label class="form-label">Display Order</label><input type="number" name="DisplayOrder" class="form-control" value="0" /></div>
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
