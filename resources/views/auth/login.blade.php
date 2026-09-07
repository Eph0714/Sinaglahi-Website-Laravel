@extends('layouts.app')

@section('title', 'Artist / Admin Login')

@section('content')

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <h1 class="auth-title">Sign In</h1>
                    <p class="text-muted mb-4">For approved artists and Sinaglahi administrators.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('account.login.submit') }}" method="post" novalidate>
                        @csrf
                        <input type="hidden" name="returnUrl" value="{{ $returnUrl }}" />

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" autocomplete="username" required autofocus />
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" class="form-control" autocomplete="current-password" required />
                        </div>
                        <div class="form-check mb-3">
                            <input id="remember" name="remember" type="checkbox" class="form-check-input" />
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary-brand w-100">Sign In</button>
                    </form>

                    <hr class="my-4" />
                    <p class="text-center small mb-0">Not an approved artist yet?</p>
                    <p class="text-center small">
                        <a href="{{ route('join.index') }}">Apply to join Sinaglahi</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
