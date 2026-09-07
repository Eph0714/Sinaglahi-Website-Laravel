@extends('layouts.app')

@section('title', 'Group Activities')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>Group Activities</h1>
        <p class="lead-muted">Exhibitions, workshops, gatherings, and community activities from Sinaglahi Artists Group Nueva Vizcaya Inc.</p>
    </div>
</section>

@if ($featured->isNotEmpty())
    <section class="section-tight">
        <div class="container">
            <div class="section-title">
                <h2>Featured Activities</h2>
            </div>
            <div class="row g-4">
                @foreach ($featured as $act)
                    <div class="col-md-4">
                        <a class="activity-feature-card" href="{{ route('group-activities.show', $act->Slug) }}">
                            <div class="activity-feature-img" style="background-image:url('{{ $act->CoverPhotoPath ?? placeholder_image(800, 600, $act->Title) }}');"></div>
                            <div class="activity-feature-overlay">
                                <div class="activity-feature-date">{{ $act->ActivityDate->format('F j, Y') }}</div>
                                <div class="activity-feature-title">{{ $act->Title }}</div>
                                <div class="activity-feature-loc">{{ $act->Location }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Latest Activities</h2>
        </div>

        <form method="get" class="filter-bar row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Activity title, location, or keyword" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="categoryId" class="form-select">
                    <option value="">All Categories</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->Id }}" @selected($c->Id == $categoryId)>{{ $c->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-brand w-100">Filter</button>
            </div>
        </form>

        @if ($activities->isEmpty())
            <div class="empty-state">
                <h3>No activities found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($activities as $act)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card-art">
                            <a class="thumb-wrap" href="{{ route('group-activities.show', $act->Slug) }}">
                                <img src="{{ $act->CoverPhotoPath ?? placeholder_image(500, 400, $act->Title) }}" alt="{{ $act->Title }}" loading="lazy" />
                            </a>
                            <div class="card-art-body">
                                <span class="activity-status-badge activity-status-{{ strtolower($act->statusLabel()) }}">{{ $act->statusLabel() }}</span>
                                <a class="card-art-title d-block mt-2" href="{{ route('group-activities.show', $act->Slug) }}">{{ $act->Title }}</a>
                                <div class="card-art-meta">{{ $act->ActivityDate->format('M j, Y') }} &middot; {{ $act->category->Name ?? '' }}</div>
                                <div class="card-art-meta">{{ $act->Location }}</div>
                                @if ($act->PhotoCount > 0)
                                    <div class="text-muted small mt-1"><i class="bi bi-camera"></i> {{ $act->PhotoCount }} photo{{ $act->PhotoCount === 1 ? '' : 's' }}</div>
                                @endif
                                <a class="small mt-auto" href="{{ route('group-activities.show', $act->Slug) }}">View Gallery &rarr;</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($totalPages > 1)
                <nav class="mt-4" aria-label="Activities pagination">
                    <ul class="pagination pagination-brand justify-content-center">
                        @for ($p = 1; $p <= $totalPages; $p++)
                            <li class="page-item {{ $p == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ route('group-activities.index', array_filter(['page' => $p, 'search' => $search, 'categoryId' => $categoryId, 'year' => $year])) }}">{{ $p }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        @endif
    </div>
</section>

@endsection
