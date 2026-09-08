@extends('layouts.app')

@section('title', 'Review & Submit')

@section('content')

<section class="section">
    <div class="container">
        <div class="join-steps mb-4">
            <div class="join-step join-step-done"><span class="join-step-dot">✓</span> <span class="join-step-label">Personal Info</span></div>
            <div class="join-step join-step-done"><span class="join-step-dot">✓</span> <span class="join-step-label">Art Background</span></div>
            <div class="join-step join-step-done"><span class="join-step-dot">✓</span> <span class="join-step-label">Artworks</span></div>
            <div class="join-step join-step-current"><span class="join-step-dot">4</span> <span class="join-step-label">Review &amp; Submit</span></div>
        </div>

        <div class="col-lg-8 mx-auto">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section review-block">
                <h2>Review Your Application</h2>
                <dl class="row">
                    <dt class="col-sm-4">Full Name</dt><dd class="col-sm-8">{{ $app->FullName }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $app->Email }}</dd>
                    <dt class="col-sm-4">Contact Number</dt><dd class="col-sm-8">{{ $app->ContactNumber }}</dd>
                    <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $app->Municipality }}, {{ $app->Province }}</dd>
                    <dt class="col-sm-4">Art Mediums</dt><dd class="col-sm-8">{{ $app->ArtMediums }}</dd>
                    <dt class="col-sm-4">Artworks</dt><dd class="col-sm-8">{{ $artworks->count() }} submitted</dd>
                </dl>
            </div>

            <div class="form-section">
                <h2>Consent &amp; Declaration</h2>
                <form action="{{ route('join.submit') }}" method="post">
                    @csrf
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="ApplicantConsent" value="1" id="applicantConsent" required>
                        <label class="form-check-label" for="applicantConsent">I certify that the information provided is accurate to the best of my knowledge.</label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="PrivacyConsent" value="1" id="privacyConsent" required>
                        <label class="form-check-label" for="privacyConsent">I agree to the collection and processing of my personal data for membership evaluation purposes.</label>
                    </div>
                    <button type="submit" class="btn btn-join btn-lg">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection
