@extends('layouts.app')

@section('title', $artwork->Title)
@section('metaDescription', "{$artwork->Title} by {$artwork->artist->ArtistName} — {$artwork->Medium}, {$artwork->Year} — Sinaglahi Artists Group Nueva Vizcaya Inc.")

@section('content')

<section class="section-tight">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <img class="artwork-detail-image lightbox-open" style="cursor:zoom-in;" src="{{ $artwork->ImagePath }}" alt="{{ $artwork->Title }}"
                     data-full="{{ $artwork->ImagePath }}" data-title="{{ $artwork->Title }}" data-meta="{{ $artwork->artist->ArtistName }} &middot; {{ $artwork->Medium }} &middot; {{ $artwork->Year }}" />
            </div>
            <div class="col-lg-5">
                <h1>{{ $artwork->Title }}</h1>
                <p class="mb-4">
                    by <a href="{{ route('artists.show', $artwork->artist->Slug) }}" class="fw-semibold">{{ $artwork->artist->ArtistName }}</a>
                </p>

                <dl class="artwork-detail-meta row">
                    <dt class="col-4">Medium</dt><dd class="col-8">{{ $artwork->Medium }}</dd>
                    <dt class="col-4">Size</dt><dd class="col-8">{{ $artwork->Size }}</dd>
                    <dt class="col-4">Year</dt><dd class="col-8">{{ $artwork->Year }}</dd>
                    @if ($artwork->Price)
                        <dt class="col-4">Price</dt><dd class="col-8">&#8369;{{ number_format($artwork->Price, 2) }}</dd>
                    @endif
                    <dt class="col-4">Availability</dt>
                    <dd class="col-8">{{ $artwork->IsAvailable ? 'Available' : 'Not currently available' }}</dd>
                </dl>

                <h3 class="mt-4">Description</h3>
                <p class="text-muted">{{ $artwork->Description }}</p>

                <a class="btn btn-outline-brand mt-2" href="{{ route('artists.show', $artwork->artist->Slug) }}">View Artist Profile</a>
            </div>
        </div>

        @if ($moreFromArtist->isNotEmpty())
            <div class="mt-5 pt-4 border-top">
                <h3>More from {{ $artwork->artist->ArtistName }}</h3>
                <div class="row g-4">
                    @foreach ($moreFromArtist as $art)
                        <div class="col-6 col-md-3">
                            <div class="card-art">
                                <div class="thumb-wrap lightbox-open" tabindex="0" role="button" aria-label="View {{ $art->Title }} full-size"
                                     data-full="{{ $art->ImagePath }}" data-title="{{ $art->Title }}" data-meta="{{ $art->Medium }} &middot; {{ $art->Year }}"
                                     data-detail="{{ route('artworks.show', $art->Slug) }}">
                                    <img src="{{ $art->ImagePath }}" alt="{{ $art->Title }}" loading="lazy" />
                                </div>
                                <div class="card-art-body">
                                    <a class="card-art-title" href="{{ route('artworks.show', $art->Slug) }}">{{ $art->Title }}</a>
                                    <div class="card-art-meta">{{ $art->Medium }} &middot; {{ $art->Year }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@endsection
