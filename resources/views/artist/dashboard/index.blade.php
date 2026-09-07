@extends('layouts.artist')

@section('title', 'Artist Dashboard')

@section('content')

<div class="dashboard-welcome">
    <h1 class="h3 mb-1">Welcome back, {{ $artist->ArtistName }}</h1>
    <p class="text-muted mb-0">Here's an overview of your artist profile and artworks.</p>
</div>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-images"></i></div><div class="stat-body"><div class="stat-value">{{ $totalArtworks }}</div><div class="stat-label">Total Artworks</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-check-circle"></i></div><div class="stat-body"><div class="stat-value">{{ $publishedArtworks }}</div><div class="stat-label">Published</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingArtworks }}</div><div class="stat-label">Pending Review</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-x-circle"></i></div><div class="stat-body"><div class="stat-value">{{ $rejectedArtworks }}</div><div class="stat-label">Rejected</div></div></div>
</div>

<div class="mt-4">
    <a class="btn btn-outline-brand" href="{{ route('artists.show', $artist->Slug) }}" target="_blank">View My Public Profile</a>
</div>

<p class="text-muted small mt-4">Profile editing and artwork management (add/edit/delete, photo crop) are ported next.</p>

@endsection
