@extends('layouts.admin')

@section('title', 'Homepage Banners')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Homepage Banners</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.banners.create') }}">Add Banner</a>
</div>
<p class="text-muted small">Add one banner for a static hero, or several for a homepage carousel. Only enabled banners appear on the site.</p>

@if (session('bannerMessage'))
    <div class="alert alert-success">{{ session('bannerMessage') }}</div>
@endif

@if ($banners->isEmpty())
    <div class="empty-state"><h3>No banners yet</h3><p>The homepage will fall back to a default hero until you add one.</p></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th></th><th>Title</th><th>Button</th><th>Order</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($banners as $b)
                    <tr>
                        <td><img class="thumb" src="{{ $b->ImagePath }}" alt="{{ $b->Title }}" /></td>
                        <td>{{ $b->Title }}<div class="text-muted small">{{ $b->Subtitle }}</div></td>
                        <td class="small">{{ $b->ButtonText }}</td>
                        <td>{{ $b->DisplayOrder }}</td>
                        <td>{{ $b->IsFeatured ? '★' : '—' }}</td>
                        <td><span class="status-badge {{ $b->IsEnabled ? 'status-published' : 'status-archived' }}">{{ $b->IsEnabled ? 'Enabled' : 'Disabled' }}</span></td>
                        <td class="row-actions">
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.banners.edit', $b->Id) }}">Edit</a>
                            <form action="{{ route('admin.banners.toggle', $b->Id) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $b->IsEnabled ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form action="{{ route('admin.banners.destroy', $b->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record and associated data. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
