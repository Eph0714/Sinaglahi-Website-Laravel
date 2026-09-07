@extends('layouts.app')

@section('title', 'Contact')

@section('content')

<section class="page-hero page-hero-compact">
    <div class="container text-center">
        <h1>Contact Us</h1>
        <p class="lead-muted">We'd love to hear from you.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <p class="text-muted">
                    For inquiries about {{ $settings->OrganizationName }}, membership,
                    or exhibitions, please reach out to the organization's administration.
                </p>
                <dl class="row justify-content-center text-start mt-4">
                    <dt class="col-sm-4">Organization</dt>
                    <dd class="col-sm-8">{{ $settings->OrganizationName }}</dd>

                    @php
                        $locationParts = array_filter([$settings->Address, $settings->Municipality, $settings->Province, $settings->Country]);
                    @endphp
                    @if (!empty($locationParts))
                        <dt class="col-sm-4">Location</dt>
                        <dd class="col-sm-8">{{ implode(', ', $locationParts) }}</dd>
                    @endif
                    @if ($settings->ContactEmail)
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><a href="mailto:{{ $settings->ContactEmail }}">{{ $settings->ContactEmail }}</a></dd>
                    @endif
                    @if ($settings->ContactNumber)
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $settings->ContactNumber }}</dd>
                    @endif
                    @if ($settings->MobileNumber)
                        <dt class="col-sm-4">Mobile</dt>
                        <dd class="col-sm-8">{{ $settings->MobileNumber }}</dd>
                    @endif
                    @if ($settings->OfficeHours)
                        <dt class="col-sm-4">Office Hours</dt>
                        <dd class="col-sm-8">{{ $settings->OfficeHours }}</dd>
                    @endif
                </dl>

                @if ($settings->GoogleMapsUrl)
                    <div class="ratio ratio-16x9 mt-4 mb-2">
                        <iframe src="{{ $settings->GoogleMapsUrl }}" style="border:0;" allowfullscreen loading="lazy"></iframe>
                    </div>
                @endif

                <a class="btn btn-join btn-lg mt-3" href="{{ route('join.index') }}">Want to Join Sinaglahi?</a>
            </div>
        </div>
    </div>
</section>

@endsection
