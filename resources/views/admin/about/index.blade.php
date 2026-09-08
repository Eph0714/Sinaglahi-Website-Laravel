@extends('layouts.admin')

@section('title', 'About Us Management')

@section('content')

<h1 class="h3 mb-4">About Us Management</h1>

@if (session('aboutMessage'))
    <div class="alert alert-success">{{ session('aboutMessage') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.about.update') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Featured Image</h2>
        @if ($about->FeaturedImagePath)
            <img src="{{ $about->FeaturedImagePath }}" class="mb-2 rounded" style="max-height:200px;" />
        @endif
        <input name="FeaturedImage" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
        <p class="text-muted small mt-1 mb-0">Shown next to "Who We Are" on the homepage.</p>
    </div>

    <div class="form-section">
        <h2>Overview</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $about->Title ?? '') }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Introduction</label>
                <textarea name="Introduction" class="form-control" rows="4">{{ old('Introduction', $about->Introduction ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Our Story / History</label>
                <textarea name="History" class="form-control" rows="4">{{ old('History', $about->History ?? '') }}</textarea>
            </div>
        </div>
        <p class="text-muted small mb-0 mt-2">Basic HTML is allowed (headings, paragraphs, bold/italic, lists, links, images, quotes). Scripts and event handlers are stripped automatically.</p>
    </div>

    <div class="form-section">
        <h2>Mission &amp; Vision</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Mission</label>
                <textarea name="Mission" class="form-control" rows="4">{{ old('Mission', $about->Mission ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Vision</label>
                <textarea name="Vision" class="form-control" rows="4">{{ old('Vision', $about->Vision ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Goals</label>
                <textarea name="Goals" class="form-control" rows="4">{{ old('Goals', $about->Goals ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Organization &amp; Leadership</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Organization Story</label>
                <textarea name="OrganizationStory" class="form-control" rows="4">{{ old('OrganizationStory', $about->OrganizationStory ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Leadership Info</label>
                <textarea name="LeadershipInfo" class="form-control" rows="4">{{ old('LeadershipInfo', $about->LeadershipInfo ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">Save Changes</button>
</form>

<div class="d-flex justify-content-between align-items-center mb-3 mt-5">
    <h2 class="h5 mb-0">Core Values</h2>
    <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#addCoreValueModal">Add Core Value</button>
</div>

@if ($coreValues->isEmpty())
    <div class="empty-state"><h3>No core values yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Title</th><th>Description</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($coreValues as $v)
                    <tr>
                        <td>{{ $v->Title }}</td>
                        <td class="small text-muted">{{ $v->Description }}</td>
                        <td>{{ $v->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $v->IsActive ? 'status-published' : 'status-archived' }}">{{ $v->IsActive ? 'Active' : 'Inactive' }}</span></td>
                        <td class="row-actions">
                            <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#editCoreValueModal-{{ $v->Id }}">Edit</button>
                            <form action="{{ route('admin.about.core-values.destroy', $v->Id) }}" method="post" class="d-inline" onsubmit="return confirm('This action will permanently delete this record. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editCoreValueModal-{{ $v->Id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.about.core-values.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="Id" value="{{ $v->Id }}" />
                                    <div class="modal-header"><h5 class="modal-title">Edit Core Value</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Title</label>
                                            <input name="Title" class="form-control" value="{{ $v->Title }}" required maxlength="100" />
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Description</label>
                                            <textarea name="Description" class="form-control" rows="2" maxlength="500">{{ $v->Description }}</textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Display Order</label>
                                            <input type="number" name="DisplayOrder" class="form-control" value="{{ $v->DisplayOrder }}" />
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="IsActive" value="1" class="form-check-input" @checked($v->IsActive) />
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

<div class="modal fade" id="addCoreValueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.about.core-values.store') }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Core Value</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title</label>
                        <input name="Title" class="form-control" required maxlength="100" />
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
                    <button type="submit" class="btn btn-primary-brand">Add Core Value</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
