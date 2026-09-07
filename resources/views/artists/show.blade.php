@extends('layouts.app')

@section('title', $artist->ArtistName)
@section('metaDescription', "{$artist->ArtistName} — {$artist->Specialization} — Sinaglahi Artists Group Nueva Vizcaya Inc.")

@section('content')

<section class="artist-profile-header">
    <div class="container">
        <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-end gap-3 pt-3">
            <img class="artist-avatar" src="{{ $artist->ProfilePhotoPath ?? placeholder_image(300, 300, $artist->ArtistName) }}" alt="Portrait of {{ $artist->ArtistName }}" />
            <div class="text-center text-sm-start">
                <h1 class="mb-1">{{ $artist->ArtistName }}</h1>
                <div class="artist-tags">
                    @if ($artist->Specialization) <span class="badge">{{ $artist->Specialization }}</span> @endif
                    @if ($artist->PreferredMedium) <span class="badge">{{ $artist->PreferredMedium }}</span> @endif
                    @if ($artist->Municipality) <span class="badge">{{ $artist->Municipality }}, {{ $artist->Province }}</span> @endif
                    @if ($artist->YearsActive) <span class="badge">{{ $artist->YearsActive }} years active</span> @endif
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                @if ($artist->Biography)
                    <h3>Biography</h3>
                    <p class="text-muted">{{ $artist->Biography }}</p>
                @endif
                @if ($artist->ArtistStatement)
                    <h3 class="mt-4">Artist Statement</h3>
                    <p class="text-muted fst-italic">&quot;{{ $artist->ArtistStatement }}&quot;</p>
                @endif
                @if ($artist->socialLinks->isNotEmpty())
                    <h3 class="mt-4">Connect</h3>
                    <ul class="list-unstyled">
                        @foreach ($artist->socialLinks as $link)
                            <li class="mb-1"><a href="{{ $link->Url }}" target="_blank" rel="noopener noreferrer">{{ $link->Platform }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="col-lg-8">
                <h3>Artworks by {{ $artist->ArtistName }}</h3>
                @if ($artworks->isEmpty())
                    <p class="text-muted">This artist has not published any artworks yet.</p>
                @else
                    <div class="row g-4">
                        @foreach ($artworks as $art)
                            <div class="col-6 col-md-4">
                                <div class="card-art">
                                    <div class="thumb-wrap lightbox-open" tabindex="0" role="button" aria-label="View {{ $art->Title }} full-size"
                                         data-full="{{ $art->ImagePath }}" data-title="{{ $art->Title }}" data-meta="{{ $art->Medium }} &middot; {{ $art->Year }}"
                                         data-detail="{{ route('artworks.show', $art->Slug) }}">
                                        <img src="{{ $art->ImagePath }}" alt="{{ $art->Title }}" loading="lazy" />
                                    </div>
                                    <div class="card-art-body">
                                        <a class="card-art-title" href="{{ route('artworks.show', $art->Slug) }}">{{ $art->Title }}</a>
                                        <div class="card-art-meta">{{ $art->Medium }} &middot; {{ $art->Year }}</div>
                                        <a class="card-art-link" href="{{ route('artworks.show', $art->Slug) }}">View Artwork &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection
