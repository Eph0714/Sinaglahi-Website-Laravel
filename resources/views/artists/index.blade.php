@extends('layouts.app')

@section('title', 'Artists')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>Sinaglahi Artists</h1>
        <p class="lead-muted">Browse the artists of Sinaglahi Artists Group Nueva Vizcaya Inc.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form method="get" class="filter-bar row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Artist name or specialization" />
            </div>
            <div class="col-md-3">
                <label class="form-label" for="municipality">Municipality</label>
                <select id="municipality" name="municipality" class="form-select">
                    <option value="">All</option>
                    @foreach ($municipalityOptions as $m)
                        <option value="{{ $m }}" @selected($m == $municipality)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="specialization">Specialization</label>
                <select id="specialization" name="specialization" class="form-select">
                    <option value="">All</option>
                    @foreach ($specializationOptions as $s)
                        <option value="{{ $s }}" @selected($s == $specialization)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
            </div>
        </form>

        @if ($artists->isEmpty())
            <div class="empty-state">
                <h3>No artists found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($artists as $artist)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card-art card-artist">
                            <div class="thumb-wrap">
                                <img src="{{ $artist->ProfilePhotoPath ?? placeholder_image(400, 400, $artist->ArtistName) }}"
                                     alt="Portrait of {{ $artist->ArtistName }}" loading="lazy" />
                            </div>
                            <div class="card-art-body text-center">
                                <div class="card-art-title">{{ $artist->ArtistName }}</div>
                                <div class="card-art-meta">
                                    {{ $artist->Specialization }}
                                    @if ($artist->Municipality) &middot; {{ $artist->Municipality }} @endif
                                </div>
                                @if ($artist->Biography)
                                    <p class="card-art-extra small">{{ \Illuminate\Support\Str::limit($artist->Biography, 160) }}</p>
                                @endif
                                <a class="btn btn-outline-brand btn-sm mt-2" href="{{ route('artists.show', $artist->Slug) }}">View Profile</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($totalPages > 1)
                <nav class="mt-4" aria-label="Artists pagination">
                    <ul class="pagination pagination-brand justify-content-center">
                        @for ($p = 1; $p <= $totalPages; $p++)
                            <li class="page-item {{ $p == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ route('artists.index', array_filter(['page' => $p, 'search' => $search, 'municipality' => $municipality, 'specialization' => $specialization])) }}">{{ $p }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        @endif

        <div class="text-center mt-5 pt-3 border-top">
            <h3>Not yet a member? Want to join Sinaglahi?</h3>
            <a class="btn btn-join btn-lg mt-2" href="{{ route('join.index') }}">Apply to Join</a>
        </div>
    </div>
</section>

@endsection
