@extends('layouts.admin')

@section('title', 'Artwork Categories')

@section('content')

<h1 class="h3 mb-4">Artwork Categories</h1>
<p class="text-muted">Categories used when artists submit artworks. Deactivating a category hides it from new submissions without deleting any artwork already using it.</p>

<div class="table-responsive">
    <table class="dash-table">
        <thead><tr><th>Name</th><th>Description</th><th>Artworks</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach ($categories as $c)
                <tr>
                    <td>{{ $c->Name }}</td>
                    <td class="small text-muted">{{ $c->Description }}</td>
                    <td>{{ $c->ArtworkCount }}</td>
                    <td><span class="status-badge {{ $c->IsActive ? 'status-published' : 'status-draft' }}">{{ $c->IsActive ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <form action="{{ route('admin.categories.toggle', $c->Id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-brand">{{ $c->IsActive ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
