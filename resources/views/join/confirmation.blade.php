@extends('layouts.app')

@section('title', 'Application Submitted')

@section('content')

<section class="section">
    <div class="container text-center">
        <div class="col-lg-6 mx-auto">
            <h1>Application Submitted!</h1>
            <p class="lead-muted">Thank you, {{ $applicantName }}. Your application has been received.</p>

            <div class="form-section review-block text-start mt-4">
                <dl class="row">
                    <dt class="col-sm-5">Reference Number</dt><dd class="col-sm-7"><strong>{{ $referenceNumber }}</strong></dd>
                    <dt class="col-sm-5">Submitted</dt><dd class="col-sm-7">{{ optional($submittedAt)->format('F j, Y g:i A') }}</dd>
                    <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ $status }}</dd>
                </dl>
            </div>

            <p class="text-muted mt-3">Please save your reference number - you'll need it (along with your registered mobile number) to check your application status.</p>
            <a class="btn btn-outline-brand mt-2" href="{{ route('join.checkStatus') }}">Check Application Status</a>
            <a class="btn btn-outline-brand mt-2" href="{{ route('home') }}">Return Home</a>
        </div>
    </div>
</section>

@endsection
