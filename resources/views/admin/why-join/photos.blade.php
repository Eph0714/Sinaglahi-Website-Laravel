@extends('layouts.admin')

@section('title', 'Why Join - Photo Gallery')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.why-join.index') }}" class="small">&larr; Back to Section Content</a>
        <h1 class="h3 mb-0">Photo Gallery</h1>
    </div>
    <a class="btn btn-primary-brand" href="{{ route('admin.why-join.photos.create') }}"><i class="bi bi-plus-lg"></i> Add Photo</a>
</div>
<p class="text-muted small">
    The <strong>Featured</strong> photo is the large lead image the public section shows first. Every other
    active photo appears as a supporting thumbnail below it, in the order set here.
</p>

@if (session('whyJoinMessage'))
    <div class="alert alert-success">{{ session('whyJoinMessage') }}</div>
@endif

@if ($photos->isEmpty())
    <div class="empty-state"><h3>No photos yet</h3><p>Add at least one photo - the first one you add automatically becomes the featured photo.</p></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Preview</th><th>Title</th><th>Status</th><th>Featured</th><th>Order</th><th>Last Updated</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($photos as $p)
                    <tr>
                        <td><img class="thumb" src="{{ $p->ImagePath }}" alt="{{ $p->Title ?? 'Photo' }}" style="width:96px;height:64px;object-fit:cover;" /></td>
                        <td>
                            {{ $p->Title ?? '(untitled)' }}
                            @if ($p->Caption)
                                <div class="text-muted small">{{ $p->Caption }}</div>
                            @endif
                        </td>
                        <td><span class="status-badge {{ $p->IsActive ? 'status-published' : 'status-archived' }}">{{ $p->IsActive ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            @if ($p->IsFeatured)
                                <span class="status-badge status-featured">★ Featured</span>
                            @else
                                <form action="{{ route('admin.why-join.photos.set-featured', $p->Id) }}" method="post">
                                    @csrf
                                    <button class="btn btn-sm btn-settings" title="Make this the featured photo"><i class="bi bi-star"></i> Set Featured</button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $p->DisplayOrder }}</td>
                        <td class="small text-muted">{{ ($p->UpdatedAt ?? $p->CreatedAt)->format('M j, Y') }}</td>
                        <td class="row-actions">
                            <form action="{{ route('admin.why-join.photos.move', $p->Id) }}" method="post" class="d-inline">
                                @csrf
                                <input type="hidden" name="direction" value="up" />
                                <button class="btn btn-sm btn-settings" title="Move up"><i class="bi bi-arrow-up"></i></button>
                            </form>
                            <form action="{{ route('admin.why-join.photos.move', $p->Id) }}" method="post" class="d-inline">
                                @csrf
                                <input type="hidden" name="direction" value="down" />
                                <button class="btn btn-sm btn-settings" title="Move down"><i class="bi bi-arrow-down"></i></button>
                            </form>
                            <a class="btn btn-sm btn-edit" href="{{ route('admin.why-join.photos.edit', $p->Id) }}" title="Edit / Replace"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.why-join.photos.toggle', $p->Id) }}" method="post" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-settings">{{ $p->IsActive ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form action="{{ route('admin.why-join.photos.destroy', $p->Id) }}" method="post" class="d-inline" onsubmit="return confirm('Permanently delete this photo? This cannot be undone.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-delete"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
