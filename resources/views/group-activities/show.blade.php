@extends('layouts.app')

@section('title', $activity->Title)
@section('metaDescription', "{$activity->Title} — {$activity->Location}, {$activity->ActivityDate->format('F j, Y')}. Photos from Sinaglahi Artists Group Nueva Vizcaya Inc.")

@section('content')

<section class="page-hero page-hero-compact">
    <div class="container text-center">
        <div class="activity-tags mb-2 d-flex justify-content-center gap-2">
            <span class="badge">{{ $activity->category->Name ?? '' }}</span>
            <span class="activity-status-badge activity-status-{{ strtolower($activity->statusLabel()) }}">{{ $activity->statusLabel() }}</span>
        </div>
        <h1>{{ $activity->Title }}</h1>
        <p class="lead-muted mb-1">{{ $activity->ActivityDate->format('F j, Y') }} &middot; {{ $activity->Location }}</p>
    </div>
</section>

@if ($activity->CoverPhotoPath)
    <div class="activity-cover-wrap">
        <img src="{{ $activity->CoverPhotoPath }}" alt="{{ $activity->Title }}" class="activity-cover-img lightbox-open"
             data-full="{{ $activity->CoverPhotoPath }}" data-title="{{ $activity->Title }}" data-meta="{{ $activity->ActivityDate->format('F j, Y') }}" />
    </div>
@endif

<section class="section-tight">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="h4">About This Activity</h2>
                <p class="text-muted">{{ $activity->Description }}</p>

                @if ($activity->FacebookUrl)
                    <a class="btn btn-outline-brand" href="{{ $activity->FacebookUrl }}" target="_blank" rel="noopener noreferrer">
                        View Original Facebook Post &nearr;
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

@if ($activity->visiblePhotos->isNotEmpty())
    <section class="section section-alt">
        <div class="container">
            <div class="section-title">
                <h2>Event Gallery</h2>
                <p><i class="bi bi-camera"></i> {{ $activity->visiblePhotos->count() }} photo{{ $activity->visiblePhotos->count() === 1 ? '' : 's' }}</p>
            </div>
            <div class="activity-masonry">
                @foreach ($activity->visiblePhotos as $photo)
                    <div class="activity-masonry-item lightbox-open" tabindex="0" role="button" aria-label="View photo full-size"
                         data-full="{{ $photo->FilePath }}" data-title="{{ $activity->Title }}"
                         data-meta="{{ $photo->Caption }}{{ $photo->PhotoDate ? ' · '.$photo->PhotoDate->format('M j, Y') : '' }}"
                         data-desc="{{ $photo->Description }}">
                        <img src="{{ $photo->ThumbnailPath ?? $photo->FilePath }}" alt="{{ $photo->Caption ?? $activity->Title }}" loading="lazy" />
                        @if ($photo->Caption)
                            <div class="activity-masonry-caption">{{ $photo->Caption }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<div class="text-center py-4">
    <a class="btn btn-outline-brand" href="{{ route('group-activities.index') }}">&larr; Back to Group Activities</a>
</div>

@endsection
