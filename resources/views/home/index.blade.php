@extends('layouts.app')

@section('title', 'Home')

@php
    $whyJoinFeatured = $whyJoinPhotos->firstWhere('IsFeatured', true) ?? $whyJoinPhotos->first();
    $whyJoinSupporting = $whyJoinPhotos->reject(fn ($p) => $whyJoinFeatured && $p->Id === $whyJoinFeatured->Id);
@endphp

@section('content')

@if ($banners->isNotEmpty())
    @if ($banners->count() === 1)
        @php $b = $banners->first(); @endphp
        <section class="hero" style="background-image:linear-gradient(160deg, rgba(20,20,20,.88) 0%, rgba(14,14,14,.94) 100%), url('{{ $b->ImagePath }}'); background-size:cover; background-position:center;">
            <div class="container">
                <span class="eyebrow">Welcome to {{ $b->Subtitle }}</span>
                <h1>{{ $b->Title }}</h1>
                @if ($b->Description)
                    <p class="subhead">{{ $b->Description }}</p>
                @endif
                <div class="btn-group-hero">
                    @if ($b->ButtonText)
                        <a class="btn btn-join" href="{{ $b->ButtonUrl }}">{{ $b->ButtonText }}</a>
                    @endif
                    @if ($b->SecondButtonText)
                        <a class="btn btn-light-outline" href="{{ $b->SecondButtonUrl }}">{{ $b->SecondButtonText }}</a>
                    @endif
                </div>
            </div>
        </section>
    @else
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach ($banners as $i => $b)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <section class="hero" style="background-image:linear-gradient(160deg, rgba(20,20,20,.88) 0%, rgba(14,14,14,.94) 100%), url('{{ $b->ImagePath }}'); background-size:cover; background-position:center;">
                            <div class="container">
                                <span class="eyebrow">Welcome to {{ $b->Subtitle }}</span>
                                <h1>{{ $b->Title }}</h1>
                                @if ($b->Description)
                                    <p class="subhead">{{ $b->Description }}</p>
                                @endif
                                <div class="btn-group-hero">
                                    @if ($b->ButtonText)
                                        <a class="btn btn-join" href="{{ $b->ButtonUrl }}">{{ $b->ButtonText }}</a>
                                    @endif
                                    @if ($b->SecondButtonText)
                                        <a class="btn btn-light-outline" href="{{ $b->SecondButtonUrl }}">{{ $b->SecondButtonText }}</a>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    @endif
@else
    <section class="hero">
        <div class="container">
            <span class="eyebrow">Welcome to Sinaglahi</span>
            <h1>Celebrating Filipino Art<br />and Creativity</h1>
            <p class="subhead">
                Sinaglahi Artists Group Nueva Vizcaya Inc. is a community of visual artists dedicated to
                creativity, mentorship, and cultural expression.
            </p>
            <div class="btn-group-hero">
                <a class="btn btn-join" href="{{ route('artworks.index') }}">Explore Artworks</a>
                <a class="btn btn-light-outline" href="{{ route('artists.index') }}">Meet Our Artists</a>
            </div>
        </div>
    </section>
@endif

<section class="section section-alt">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow">Who We Are</span>
                <h2>{{ $about->Title ?? 'About Sinaglahi' }}</h2>
                @php
                    $introText = trim(strip_tags($about->Introduction ?? ''));
                    if ($introText === '') {
                        $introText = 'Sinaglahi Artists Group Nueva Vizcaya Inc. brings together painters, sculptors, '
                            .'photographers, and mixed-media artists who share a passion for creativity and '
                            .'cultural expression. Through exhibitions, mentorship, and community events, the '
                            .'organization supports its members in growing their craft and sharing their work with the public.';
                    } elseif (mb_strlen($introText) > 400) {
                        $introText = mb_substr($introText, 0, 400).'…';
                    }
                @endphp
                <p class="text-muted">{{ $introText }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-brand" href="{{ route('home.about') }}">Learn More About Us</a>
                    <a class="btn btn-outline-brand" href="{{ route('artists.index') }}">Artists</a>
                    <a class="btn btn-outline-brand" href="{{ route('artworks.index') }}">Gallery</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="thumb-wrap" style="border-radius: var(--radius); overflow:hidden;">
                    <img src="{{ $about->FeaturedImagePath ?? placeholder_image(800, 500, 'Sinaglahi') }}" alt="Sinaglahi community" class="w-100" style="display:block; height:auto;" />
                </div>
            </div>
        </div>
    </div>
</section>

@if ($homepageActivities->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-title">
                <span class="eyebrow">Creating Together. Inspiring One Another.</span>
                <h2>Sinaglahi Group Activities</h2>
                <p>Exhibitions, workshops, painting sessions, and community gatherings from Sinaglahi Artists Group Nueva Vizcaya Inc.</p>
            </div>
            <div class="row g-4">
                @foreach ($homepageActivities as $act)
                    <div class="col-md-6 col-lg-4">
                        <div class="card-art">
                            <a class="thumb-wrap" href="{{ route('group-activities.show', $act->Slug) }}">
                                <img src="{{ $act->CoverPhotoPath ?? placeholder_image(800, 600, $act->Title) }}" alt="{{ $act->Title }}" loading="lazy" />
                            </a>
                            <div class="card-art-body">
                                <span class="activity-status-badge activity-status-{{ strtolower($act->statusLabel()) }}">{{ $act->statusLabel() }}</span>
                                <a class="card-art-title d-block mt-2" href="{{ route('group-activities.show', $act->Slug) }}">{{ $act->Title }}</a>
                                <div class="card-art-meta">{{ $act->ActivityDate->format('F j, Y') }} &middot; {{ $act->Location }}</div>
                                @if ($act->Description)
                                    <p class="card-art-extra">{{ \Illuminate\Support\Str::limit($act->Description, 140) }}</p>
                                @endif
                                @if ($act->PhotoCount > 0)
                                    <div class="text-muted small mb-2"><i class="bi bi-camera"></i> {{ $act->PhotoCount }} photo{{ $act->PhotoCount === 1 ? '' : 's' }}</div>
                                @endif
                                <a class="card-art-link mt-auto" href="{{ route('group-activities.show', $act->Slug) }}">View Activity &rarr;</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a class="btn btn-primary-brand btn-lg" href="{{ route('group-activities.index') }}">View All Activities</a>
            </div>
        </div>
    </section>
@endif

<section class="why-join-section">
    <div class="container">
        <div class="why-join-header">
            <span class="eyebrow">{{ $whyJoin->Subtitle }}</span>
            <h2>{{ $whyJoin->Title }}</h2>
            <p class="why-join-intro">{{ $whyJoin->Introduction }}</p>
        </div>

        @if ($whyJoinFeatured)
            <div class="why-join-gallery">
                <div class="why-join-featured">
                    <img src="{{ $whyJoinFeatured->ImagePath }}" alt="{{ $whyJoinFeatured->Title ?? 'Sinaglahi Artists' }}" loading="lazy" />
                    @if ($whyJoinFeatured->Title || $whyJoinFeatured->Caption)
                        <div class="why-join-featured-caption">
                            @if ($whyJoinFeatured->Title)
                                <div class="caption-title">{{ $whyJoinFeatured->Title }}</div>
                            @endif
                            @if ($whyJoinFeatured->Caption)
                                <div class="caption-text">{{ $whyJoinFeatured->Caption }}</div>
                            @endif
                        </div>
                    @endif
                </div>
                @if ($whyJoinSupporting->isNotEmpty())
                    <div class="why-join-supporting">
                        @foreach ($whyJoinSupporting as $photo)
                            <img src="{{ $photo->ImagePath }}" alt="{{ $photo->Title ?? 'Sinaglahi Artists' }}" loading="lazy" title="{{ $photo->Title }}" />
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($whyJoinBenefits->isNotEmpty())
            <div class="why-join-benefits">
                @foreach ($whyJoinBenefits as $benefit)
                    <div class="why-join-benefit-card">
                        <div class="why-join-benefit-icon">{{ $benefit->Icon }}</div>
                        <h3>{{ $benefit->Title }}</h3>
                        <p>{{ $benefit->Description }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="why-join-statement">
            <h3>&quot;{{ $whyJoin->FeaturedStatement }}&quot;</h3>
            <p>{{ $whyJoin->SupportingParagraph }}</p>
            <a class="btn btn-join btn-lg" href="{{ $whyJoin->ButtonUrl }}">{{ $whyJoin->ButtonText }}</a>
        </div>
    </div>
</section>

@if ($testimonials->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="section-title">
                <span class="eyebrow">In Their Words</span>
                <h2>What Our Members Say</h2>
            </div>
            <div class="row g-4">
                @foreach ($testimonials as $t)
                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <p class="testimonial-quote">&quot;{{ $t->Quote }}&quot;</p>
                            <div class="testimonial-author">
                                <img src="{{ $t->ImagePath ?? placeholder_image(100, 100, $t->Name) }}" alt="{{ $t->Name }}" />
                                <div>
                                    <div class="testimonial-name">{{ $t->Name }}</div>
                                    @if ($t->Position)
                                        <div class="testimonial-position">{{ $t->Position }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if ($categories->isNotEmpty())
    <section class="section">
        <div class="container text-center">
            <div class="section-title">
                <span class="eyebrow">Browse by Medium</span>
                <h2>Art Categories</h2>
            </div>
            <div>
                @foreach ($categories as $cat)
                    <a class="category-chip" href="{{ route('artworks.index', ['categoryId' => $cat->Id]) }}">{{ $cat->Name }}</a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
