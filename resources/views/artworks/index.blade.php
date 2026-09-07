@extends('layouts.app')

@section('title', 'Gallery')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>Artwork Gallery</h1>
        <p class="lead-muted">Browse published works from Sinaglahi artists.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form method="get" class="filter-bar row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Title or artist" />
            </div>
            <div class="col-md-3">
                <label class="form-label" for="medium">Medium</label>
                <select id="medium" name="medium" class="form-select">
                    <option value="">All</option>
                    @foreach ($mediumOptions as $m)
                        <option value="{{ $m }}" @selected($m == $medium)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="year">Year</label>
                <select id="year" name="year" class="form-select">
                    <option value="">All</option>
                    @foreach ($yearOptions as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="categoryId">Category</label>
                <select id="categoryId" name="categoryId" class="form-select">
                    <option value="">All</option>
                    @foreach ($categoryOptions as $c)
                        <option value="{{ $c->Id }}" @selected($c->Id == $categoryId)>{{ $c->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
            </div>
        </form>

        @if ($artworks->isEmpty())
            <div class="empty-state">
                <h3>No artworks found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($artworks as $art)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card-art">
                            <div class="thumb-wrap lightbox-open" tabindex="0" role="button" aria-label="View {{ $art->Title }} full-size"
                                 data-full="{{ $art->ImagePath }}" data-title="{{ $art->Title }}" data-meta="{{ $art->artist->ArtistName ?? '' }} &middot; {{ $art->Medium }} &middot; {{ $art->Year }}"
                                 data-detail="{{ route('artworks.show', $art->Slug) }}">
                                <img src="{{ $art->ImagePath }}" alt="{{ $art->Title }}" loading="lazy" />
                            </div>
                            <div class="card-art-body">
                                <a class="card-art-title" href="{{ route('artworks.show', $art->Slug) }}">{{ $art->Title }}</a>
                                <div class="card-art-meta">
                                    <a href="{{ route('artists.show', $art->artist->Slug ?? '') }}">{{ $art->artist->ArtistName ?? '' }}</a>
                                    &middot; {{ $art->Medium }} &middot; {{ $art->Year }}
                                </div>
                                <a class="card-art-link" href="{{ route('artworks.show', $art->Slug) }}">View Artwork &rarr;</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($totalPages > 1)
                <nav class="mt-4" aria-label="Gallery pagination">
                    <ul class="pagination pagination-brand justify-content-center">
                        @for ($p = 1; $p <= $totalPages; $p++)
                            <li class="page-item {{ $p == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ route('artworks.index', array_filter(['page' => $p, 'search' => $search, 'medium' => $medium, 'year' => $year, 'categoryId' => $categoryId])) }}">{{ $p }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        @endif
    </div>
</section>

@endsection
