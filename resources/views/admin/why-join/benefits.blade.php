@extends('layouts.admin')

@section('title', 'Why Join - Benefit Cards')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.why-join.index') }}" class="small">&larr; Back to Section Content</a>
        <h1 class="h3 mb-0">Benefit Cards</h1>
    </div>
    <a class="btn btn-primary-brand" href="{{ route('admin.why-join.benefits.create') }}"><i class="bi bi-plus-lg"></i> Add Benefit Card</a>
</div>
<p class="text-muted small">These cards appear in order below the section introduction. Only active cards are shown publicly.</p>

@if (session('whyJoinMessage'))
    <div class="alert alert-success">{{ session('whyJoinMessage') }}</div>
@endif

@if ($benefits->isEmpty())
    <div class="empty-state"><h3>No benefit cards yet</h3><p>Add at least one so the section has something to show.</p></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th></th><th>Title</th><th>Description</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($benefits as $b)
                    <tr>
                        <td style="font-size:1.6rem;">{{ $b->Icon }}</td>
                        <td>{{ $b->Title }}</td>
                        <td class="small text-muted" style="max-width:360px;">{{ $b->Description }}</td>
                        <td>{{ $b->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $b->IsActive ? 'status-published' : 'status-archived' }}">{{ $b->IsActive ? 'Active' : 'Inactive' }}</span></td>
                        <td class="row-actions">
                            <form action="{{ route('admin.why-join.benefits.move', $b->Id) }}" method="post" class="d-inline">
                                @csrf
                                <input type="hidden" name="direction" value="up" />
                                <button class="btn btn-sm btn-settings" title="Move up"><i class="bi bi-arrow-up"></i></button>
                            </form>
                            <form action="{{ route('admin.why-join.benefits.move', $b->Id) }}" method="post" class="d-inline">
                                @csrf
                                <input type="hidden" name="direction" value="down" />
                                <button class="btn btn-sm btn-settings" title="Move down"><i class="bi bi-arrow-down"></i></button>
                            </form>
                            <a class="btn btn-sm btn-edit" href="{{ route('admin.why-join.benefits.edit', $b->Id) }}"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.why-join.benefits.destroy', $b->Id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this benefit card? This cannot be undone.');">
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
