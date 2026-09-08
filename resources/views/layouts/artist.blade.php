<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Artist Dashboard') &middot; Sinaglahi</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('lib/bootstrap/dist/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/site.css') }}" />
    <link rel="stylesheet" href="{{ asset_v('css/dashboard.css') }}" />
</head>
<body class="dashboard-body">
    <header class="dashboard-topbar">
        <div class="dashboard-topbar-inner">
            <button type="button" class="sidebar-toggle d-md-none" data-no-auto-style aria-label="Toggle navigation" aria-expanded="false" aria-controls="dashboardSidebar">
                <i class="bi bi-list"></i>
            </button>
            <a class="dashboard-brand" href="{{ route('home') }}">Sinaglahi</a>
            <span class="dashboard-topbar-title d-none d-sm-inline">Artist Dashboard</span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white small dashboard-user-email">{{ auth()->user()->artist->ArtistName ?? auth()->user()->Email }}</span>
                <form action="{{ route('account.logout') }}" method="post" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Log Out</button>
                </form>
            </div>
        </div>
    </header>

    <div class="dashboard-shell">
        <div class="sidebar-backdrop" data-sidebar-backdrop></div>
        <nav class="dashboard-sidebar" id="dashboardSidebar">
            <a href="{{ route('artist.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('artist.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('artist.profile.edit') }}" class="dashboard-nav-link {{ request()->routeIs('artist.profile.*') ? 'active' : '' }}">
                <i class="bi bi-person"></i> Edit Profile
            </a>
            <a href="{{ route('artist.artworks.index') }}" class="dashboard-nav-link {{ request()->routeIs('artist.artworks.*') ? 'active' : '' }}">
                <i class="bi bi-images"></i> My Artworks
            </a>
        </nav>

        <main class="dashboard-content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('lib/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset_v('js/admin-ui-enhance.js') }}"></script>
    <script src="{{ asset_v('js/sidebar-toggle.js') }}"></script>
    @stack('scripts')
</body>
</html>
