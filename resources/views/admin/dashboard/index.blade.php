@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')

<div class="dashboard-welcome">
    <h1 class="h3 mb-1">{{ auth()->user()->isSuperAdmin() ? 'Super-Admin Dashboard' : 'Admin Dashboard' }}</h1>
    <p class="text-muted mb-0">An overview of everything happening across the site right now.</p>
</div>

<div class="dashboard-section-title">Artists &amp; Membership</div>
<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-people"></i></div><div class="stat-body"><div class="stat-value">{{ $totalArtists }}</div><div class="stat-label">Total Artists</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingRegistrations }}</div><div class="stat-label">Pending Registrations</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingJoinApplications }}</div><div class="stat-label">Pending Join Applications</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-plus"></i></div><div class="stat-body"><div class="stat-value">{{ $newApplicationsThisMonth }}</div><div class="stat-label">New Applications This Month</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-patch-check"></i></div><div class="stat-body"><div class="stat-value">{{ $verifiedArtists }}</div><div class="stat-label">Verified Artists</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-person-check"></i></div><div class="stat-body"><div class="stat-value">{{ $activeArtists }}</div><div class="stat-label">Active Artists</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-person-x"></i></div><div class="stat-body"><div class="stat-value">{{ $inactiveArtists }}</div><div class="stat-label">Inactive Artists</div></div></div>
</div>

<div class="dashboard-section-title">Artworks</div>
<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-images"></i></div><div class="stat-body"><div class="stat-value">{{ $totalArtworks }}</div><div class="stat-label">Total Artworks</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-body"><div class="stat-value">{{ $pendingArtworkSubmissions }}</div><div class="stat-label">Pending Submissions</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-check-circle"></i></div><div class="stat-body"><div class="stat-value">{{ $publishedArtworks }}</div><div class="stat-label">Published Artworks</div></div></div>
</div>

<div class="dashboard-section-title">Group Activities</div>
<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-event"></i></div><div class="stat-body"><div class="stat-value">{{ $totalActivities }}</div><div class="stat-label">Total Activities</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-eye"></i></div><div class="stat-body"><div class="stat-value">{{ $publishedActivities }}</div><div class="stat-label">Published</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-eye-slash"></i></div><div class="stat-body"><div class="stat-value">{{ $draftActivities }}</div><div class="stat-label">Draft</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-star"></i></div><div class="stat-body"><div class="stat-value">{{ $featuredActivities }}</div><div class="stat-label">Featured</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-camera"></i></div><div class="stat-body"><div class="stat-value">{{ $totalActivityPhotos }}</div><div class="stat-label">Total Photos</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-calendar3"></i></div><div class="stat-body"><div class="stat-value">{{ $activitiesThisYear }}</div><div class="stat-label">This Year</div></div></div>
</div>

<p class="text-muted small mt-4">Full content management (Artists, Artworks, Activities, Applications, Website Settings, Users &amp; Permissions) is being ported module by module.</p>

@endsection
