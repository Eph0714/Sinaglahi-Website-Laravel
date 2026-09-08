@extends('layouts.app')

@section('title', 'Want to Join Sinaglahi?')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>Want to Join Sinaglahi?</h1>
        <p class="lead-muted">Apply to become a member of Sinaglahi Artists Group Nueva Vizcaya Inc. The application takes about 15-20 minutes and requires 5-10 sample artworks.</p>
    </div>
</section>

<section class="section">
    <div class="container text-center">
        <div class="col-lg-8 mx-auto">
            <div class="form-section text-start">
                <h2>Before You Begin</h2>
                <ul>
                    <li>You'll provide personal information and current affiliations.</li>
                    <li>You'll describe your art background and answer a few questions about why you want to join.</li>
                    <li>You'll upload 5 to 10 sample artworks.</li>
                    <li>You'll review everything and submit your consent.</li>
                </ul>
            </div>

            @if ($hasDraftInProgress)
                <p class="text-muted">You have an application in progress.</p>
            @endif

            <form action="{{ route('join.start') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-join btn-lg">{{ $hasDraftInProgress ? 'Continue Application' : 'Start Application' }}</button>
            </form>

            <p class="mt-4 small">
                Already applied? <a href="{{ route('join.checkStatus') }}">Check your application status</a>
            </p>
        </div>
    </div>
</section>

@endsection
