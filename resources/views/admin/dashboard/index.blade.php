@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard-welcome">
    <h1 class="h3 mb-1">{{ auth()->user()->isSuperAdmin() ? 'Super-Admin Dashboard' : 'Admin Dashboard' }}</h1>
    <p class="text-muted mb-0">An overview of everything happening across the site right now.</p>
</div>

<div class="dashboard-section-title">Artists &amp; Membership</div>
<div class="stat-grid">
    <a href="{{ route('admin.artists.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-people"></i></div><div class="stat-body"><div class="stat-value">{{ $totalArtists }}</div><div class="stat-label">Total Artists</div></div></a>
    <a href="{{ route('admin.artists.index', ['filter' => 'Pending']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingRegistrations }}</div><div class="stat-label">Pending Registrations</div></div></a>
    <a href="{{ route('admin.applications.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingJoinApplications }}</div><div class="stat-label">Pending Join Applications</div></div></a>
    <a href="{{ route('admin.applications.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-plus"></i></div><div class="stat-body"><div class="stat-value">{{ $newApplicationsThisMonth }}</div><div class="stat-label">New Applications This Month</div></div></a>
    <a href="{{ route('admin.artists.index', ['filter' => 'Verified']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-patch-check"></i></div><div class="stat-body"><div class="stat-value">{{ $verifiedArtists }}</div><div class="stat-label">Verified Artists</div></div></a>
    <a href="{{ route('admin.artists.index', ['filter' => 'Active']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-person-check"></i></div><div class="stat-body"><div class="stat-value">{{ $activeArtists }}</div><div class="stat-label">Active Artists</div></div></a>
    <a href="{{ route('admin.artists.index', ['filter' => 'Inactive']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-person-x"></i></div><div class="stat-body"><div class="stat-value">{{ $inactiveArtists }}</div><div class="stat-label">Inactive Artists</div></div></a>
</div>

<div class="dashboard-section-title">Artworks</div>
<div class="stat-grid">
    <a href="{{ route('admin.artworks.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-images"></i></div><div class="stat-body"><div class="stat-value">{{ $totalArtworks }}</div><div class="stat-label">Total Artworks</div></div></a>
    <a href="{{ route('admin.artworks.index', ['filter' => 'Pending']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingArtworkSubmissions }}</div><div class="stat-label">Pending Submissions</div></div></a>
    <a href="{{ route('admin.artworks.index', ['filter' => 'Published']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-check-circle"></i></div><div class="stat-body"><div class="stat-value">{{ $publishedArtworks }}</div><div class="stat-label">Published Artworks</div></div></a>
</div>

<div class="dashboard-section-title">Group Activities</div>
<div class="stat-grid">
    <a href="{{ route('admin.activities.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-event"></i></div><div class="stat-body"><div class="stat-value">{{ $totalActivities }}</div><div class="stat-label">Total Activities</div></div></a>
    <a href="{{ route('admin.activities.index', ['filter' => 'Published']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-eye"></i></div><div class="stat-body"><div class="stat-value">{{ $publishedActivities }}</div><div class="stat-label">Published</div></div></a>
    <a href="{{ route('admin.activities.index', ['filter' => 'Draft']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-eye-slash"></i></div><div class="stat-body"><div class="stat-value">{{ $draftActivities }}</div><div class="stat-label">Draft</div></div></a>
    <a href="{{ route('admin.activities.index', ['filter' => 'Featured']) }}" class="stat-card"><div class="stat-icon"><i class="bi bi-star"></i></div><div class="stat-body"><div class="stat-value">{{ $featuredActivities }}</div><div class="stat-label">Featured</div></div></a>
    <a href="{{ route('admin.activities.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-camera"></i></div><div class="stat-body"><div class="stat-value">{{ $totalActivityPhotos }}</div><div class="stat-label">Total Photos</div></div></a>
    <a href="{{ route('admin.activities.index') }}" class="stat-card"><div class="stat-icon"><i class="bi bi-calendar3"></i></div><div class="stat-body"><div class="stat-value">{{ $activitiesThisYear }}</div><div class="stat-label">This Year</div></div></a>
</div>

@endsection
