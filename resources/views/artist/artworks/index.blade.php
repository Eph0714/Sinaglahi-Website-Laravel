@extends('layouts.artist')

@section('title', 'My Artworks')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Artworks</h1>
    <a class="btn btn-primary-brand" href="{{ route('artist.artworks.create') }}">+ Add Artwork</a>
</div>

@if (session('artworkSaved'))
    <div class="alert alert-success">{{ session('artworkSaved') }}</div>
@endif

@if ($artworks->isEmpty())
    <div class="empty-state">
        <h3>No artworks yet</h3>
        <p>Add your first artwork to get started.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Medium</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($artworks as $art)
                    <tr>
                        <td><img class="thumb" src="{{ $art->ImagePath }}" alt="{{ $art->Title }}" /></td>
                        <td>{{ $art->Title }}</td>
                        <td>{{ $art->Medium }}</td>
                        <td>{{ $art->Year }}</td>
                        <td><span class="status-badge status-{{ strtolower($art->statusLabel()) }}">{{ $art->statusLabel() }}</span></td>
                        <td>{{ $art->SubmittedAt->format('M j, Y') }}</td>
                        <td class="d-flex gap-1 flex-wrap">
                            @if (in_array((int) $art->Status, [0, 1, 3]))
                                <a class="btn btn-sm btn-outline-brand" href="{{ route('artist.artworks.edit', $art->Id) }}">Edit</a>
                            @endif
                            @if ((int) $art->Status === 0)
                                <form action="{{ route('artist.artworks.submit', $art->Id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-brand">Submit</button>
                                </form>
                            @endif
                            @if ((int) $art->Status === 1)
                                <form action="{{ route('artist.artworks.withdraw', $art->Id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Withdraw</button>
                                </form>
                            @endif
                            <form action="{{ route('artist.artworks.destroy', $art->Id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this artwork? This cannot be undone.');">
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
