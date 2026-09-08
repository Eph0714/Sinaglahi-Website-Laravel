@extends('layouts.app')

@section('title', $page->SeoTitle ?? $page->Title)
@section('metaDescription', $page->SeoDescription ?? '')

@section('content')

<section class="page-hero page-hero-compact">
    <div class="container text-center">
        <h1>{{ $page->Title }}</h1>
    </div>
</section>

@if ($page->FeaturedImagePath)
    <div class="activity-cover-wrap">
        <img src="{{ $page->FeaturedImagePath }}" alt="{{ $page->Title }}" class="activity-cover-img" />
    </div>
@endif

<section class="section">
    <div class="container">
        <div class="col-lg-8 mx-auto rich-content">
            {!! $page->Content !!}
        </div>
    </div>
</section>

@endsection
