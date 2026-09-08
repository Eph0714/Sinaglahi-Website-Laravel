@extends('layouts.app')

@section('title', 'Check Application Status')

@section('content')

<section class="page-hero page-hero-compact">
    <div class="container text-center">
        <h1>Check Application Status</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="col-lg-6 mx-auto">
            <div class="status-lookup-intro mb-4">
                <strong>Note:</strong> For your privacy, we require your registered mobile number in addition to your reference number (or name + email if you don't have it).
            </div>

            @if (session('checkStatusError'))
                <div class="alert alert-danger">{{ session('checkStatusError') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('join.checkStatus.submit') }}" method="post" novalidate id="checkStatusForm">
                @csrf
                <div class="mb-3" id="refNumberWrap">
                    <label class="form-label">Application Reference Number</label>
                    <input name="ReferenceNumber" class="form-control" placeholder="SIN-2026-XXXXXX" value="{{ old('ReferenceNumber') }}" />
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="NoReferenceNumber" value="1" id="noRefNumber" @checked(old('NoReferenceNumber'))>
                    <label class="form-check-label" for="noRefNumber">I do not have my Application Reference Number</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Registered Mobile Number</label>
                    <input name="ContactNumber" class="form-control" value="{{ old('ContactNumber') }}" required />
                </div>
                <div class="mb-3" id="nameEmailWrap" hidden>
                    <label class="form-label">Full Name</label>
                    <input name="FullName" class="form-control" value="{{ old('FullName') }}" />
                </div>
                <div class="mb-3" id="emailWrap" hidden>
                    <label class="form-label">Email Address</label>
                    <input name="Email" type="email" class="form-control" value="{{ old('Email') }}" />
                </div>
                <button type="submit" class="btn btn-primary-brand btn-lg">Check Status</button>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    (function () {
        var cb = document.getElementById('noRefNumber');
        var refWrap = document.getElementById('refNumberWrap');
        var nameWrap = document.getElementById('nameEmailWrap');
        var emailWrap = document.getElementById('emailWrap');
        function sync() {
            refWrap.hidden = cb.checked;
            nameWrap.hidden = !cb.checked;
            emailWrap.hidden = !cb.checked;
        }
        cb.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
