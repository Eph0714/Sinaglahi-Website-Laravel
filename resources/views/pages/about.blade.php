@extends('layouts.app')

@section('title', 'About Us')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>{{ $about->Title ?? 'About Sinaglahi' }}</h1>
        <p class="lead-muted">A community built on creativity, mentorship, and cultural expression.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8 mx-auto">
                @if ($about?->Introduction)
                    <div class="rich-content">{!! $about->Introduction !!}</div>
                @endif

                @if ($about?->History)
                    <h2 class="mt-5">Our Story</h2>
                    <div class="rich-content">{!! $about->History !!}</div>
                @endif

                @if ($about?->Mission)
                    <h2 class="mt-5">Our Mission</h2>
                    <div class="rich-content">{!! $about->Mission !!}</div>
                @endif

                @if ($about?->Vision)
                    <h2 class="mt-5">Our Vision</h2>
                    <div class="rich-content">{!! $about->Vision !!}</div>
                @endif

                @if ($coreValues->isNotEmpty())
                    <h2 class="mt-5">Our Core Values</h2>
                    <div class="row g-3 mt-1">
                        @foreach ($coreValues as $v)
                            <div class="col-md-6">
                                <div class="core-value-card">
                                    <h3 class="h6">{{ $v->Title }}</h3>
                                    @if ($v->Description)
                                        <p class="text-muted small mb-0">{{ $v->Description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($about?->Goals)
                    <h2 class="mt-5">Our Goals</h2>
                    <div class="rich-content">{!! $about->Goals !!}</div>
                @endif

                @if ($about?->OrganizationStory)
                    <h2 class="mt-5">Organization Story</h2>
                    <div class="rich-content">{!! $about->OrganizationStory !!}</div>
                @endif

                @if ($about?->LeadershipInfo)
                    <h2 class="mt-5">Leadership</h2>
                    <div class="rich-content">{!! $about->LeadershipInfo !!}</div>
                @endif

                <h2 class="mt-5">Membership</h2>
                <p class="text-muted">
                    Sinaglahi welcomes artists of all backgrounds and experience levels, as well as
                    supporters and enthusiasts who share our love of art. Every application is reviewed
                    by the organization's administration to ensure a supportive and engaged community.
                </p>
                <a class="btn btn-join btn-lg mt-2" href="{{ route('join.index') }}">Apply to Join the Group</a>
            </div>
        </div>
    </div>
</section>

@endsection
