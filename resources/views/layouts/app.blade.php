<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Home') &middot; {{ $settings->ShortName ?? $settings->WebsiteName }}</title>
    <meta name="description" content="@yield('metaDescription', $settings->SeoMetaDescription ?? $settings->Description ?? 'A community of visual artists.')" />
    @if (!empty($settings->SeoKeywords))
        <meta name="keywords" content="{{ $settings->SeoKeywords }}" />
    @endif
    <meta property="og:title" content="@yield('title', $settings->SeoTitle ?? $settings->WebsiteName)" />
    <meta property="og:description" content="@yield('metaDescription', $settings->SeoMetaDescription ?? $settings->Description ?? '')" />
    <meta property="og:type" content="website" />
    @hasSection('ogImage')
        <meta property="og:image" content="@yield('ogImage')" />
    @elseif(!empty($settings->SeoDefaultOgImagePath))
        <meta property="og:image" content="{{ $settings->SeoDefaultOgImagePath }}" />
    @endif
    @if (!empty($settings->FaviconPath))
        <link rel="icon" href="{{ $settings->FaviconPath }}" />
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('lib/bootstrap/dist/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/site.css') }}" />
    @stack('styles')
</head>
<body>
    <a class="visually-hidden-focusable skip-link" href="#main-content">Skip to main content</a>

    <header class="site-header">
        <nav class="navbar navbar-expand-lg navbar-dark site-navbar">
            <div class="container">
                <a class="navbar-brand site-brand" href="{{ route('home') }}">
                    @if (!empty($settings->LogoPath))
                        <img src="{{ $settings->LogoPath }}" alt="{{ $settings->OrganizationName }}" class="brand-logo-img" />
                    @else
                        <span class="brand-mark">{{ $settings->ShortName ?? $settings->WebsiteName }}</span>
                        @if (!empty($settings->Tagline))
                            <span class="brand-sub">{{ $settings->Tagline }}</span>
                        @endif
                    @endif
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @foreach ($mainNav as $item)
                            @php
                                $isActive = $item->Url === '/' ? request()->is('/') : request()->is(ltrim($item->Url, '/').'*');
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ $isActive ? 'active-page' : '' }}"
                                   href="{{ $item->Url }}" target="{{ $item->OpenInNewTab ? '_blank' : '_self' }}"
                                   rel="{{ $item->OpenInNewTab ? 'noopener noreferrer' : '' }}">{{ $item->Label }}</a>
                            </li>
                        @endforeach
                    </ul>
                    <ul class="navbar-nav align-items-lg-center gap-lg-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('account.login') }}">Artist Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-join btn-sm" href="{{ route('join.index') }}">Want to Join {{ $settings->ShortName ?? 'Us' }}?</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content" role="main">
        @unless (request()->is('/'))
            <div class="container page-back-bar">
                <button type="button" class="back-btn" onclick="sinaglahiGoBack('{{ route('home') }}')">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
            </div>
        @endunless
        @yield('content')
    </main>

    <section class="join-band">
        <div class="container text-center">
            <span class="eyebrow" style="color: #fff;">Get Involved</span>
            <h2>Are you passionate about art and creativity?</h2>
            <p>Join {{ $settings->OrganizationName }} and become part of a community that supports artists, creativity, and cultural expression.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
                <a class="btn btn-light-outline btn-sm" href="{{ route('artworks.index') }}">Explore Artworks</a>
                <a class="btn btn-light-outline btn-sm" href="{{ route('artists.index') }}">Discover Artists</a>
                <a class="btn btn-light-outline btn-sm" href="{{ route('group-activities.index') }}">Join Our Activities</a>
                <a class="btn btn-light-outline btn-sm" href="{{ route('home.contact') }}">Contact Us</a>
            </div>
            <a class="btn btn-join btn-lg" href="{{ route('join.index') }}">Submit Your Application</a>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4">
                    <h3 class="footer-brand">{{ $settings->ShortName ?? $settings->WebsiteName }}</h3>
                    <p class="text-muted small">{{ $settings->FooterText ?? $settings->Description }}</p>
                    @if ($socialLinks->isNotEmpty())
                        <ul class="footer-social-links">
                            @foreach ($socialLinks as $link)
                                <li><a href="{{ $link->Url }}" target="_blank" rel="noopener noreferrer">{{ $link->Platform }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="col-md-4">
                    <h4>Explore</h4>
                    <ul class="footer-links">
                        @foreach ($footerExplore as $item)
                            <li><a href="{{ $item->Url }}" target="{{ $item->OpenInNewTab ? '_blank' : '_self' }}">{{ $item->Label }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <h4>Membership</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('join.index') }}" class="fw-semibold">Want to join {{ $settings->ShortName ?? 'us' }}?</a></li>
                        <li><a href="{{ route('join.checkStatus') }}">Check Application Status</a></li>
                        @foreach ($footerMembership as $item)
                            <li><a href="{{ $item->Url }}" target="{{ $item->OpenInNewTab ? '_blank' : '_self' }}">{{ $item->Label }}</a></li>
                        @endforeach
                        <li><a href="{{ route('account.register') }}">Artist Registration</a></li>
                        <li><a href="{{ route('account.login') }}">Artist Login</a></li>
                        <li><a href="{{ route('home.privacy') }}">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr />
            <p class="text-center small text-muted mb-0">{{ $settings->CopyrightText ?? ('© '.date('Y').' '.$settings->OrganizationName.'. All rights reserved.') }}</p>
        </div>
    </footer>

    <script src="{{ asset('lib/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/site.js') }}"></script>
    <script src="{{ asset('js/lightbox.js') }}"></script>
    <script src="{{ asset('js/back-button.js') }}"></script>
    @stack('scripts')
</body>
</html>
