<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin') &middot; Admin &middot; Sinaglahi</title>
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
            <span class="dashboard-topbar-title d-none d-sm-inline">{{ auth()->user()->isSuperAdmin() ? 'Super-Admin' : 'Admin' }}</span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white small dashboard-user-email">{{ auth()->user()->DisplayName ?? auth()->user()->Email }}</span>
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
            <a href="{{ route('admin.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <div class="dashboard-nav-heading">Members &amp; Artworks</div>
            <a href="{{ route('admin.artists.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.artists.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Artists
            </a>
            <a href="{{ route('admin.artworks.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.artworks.*') ? 'active' : '' }}">
                <i class="bi bi-images"></i> Artworks
            </a>
            <a href="{{ route('admin.activities.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Group Activities
            </a>
            <a href="{{ route('admin.applications.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Membership Applications
            </a>
            <div class="dashboard-nav-heading">Catalog</div>
            <a href="{{ route('admin.categories.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Artwork Categories
            </a>
            <a href="{{ route('admin.art-mediums.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.art-mediums.*') ? 'active' : '' }}">
                <i class="bi bi-palette"></i> Art Mediums
            </a>
            <a href="{{ route('admin.activity-categories.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.activity-categories.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Activity Categories
            </a>
            <div class="dashboard-nav-heading">Website</div>
            <a href="{{ route('admin.settings.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> General Settings &amp; Logo
            </a>
            <a href="{{ route('admin.settings.social-links') }}" class="dashboard-nav-link {{ request()->routeIs('admin.settings.social-links*') ? 'active' : '' }}">
                <i class="bi bi-share"></i> Social Media
            </a>
            <a href="{{ route('admin.banners.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <i class="bi bi-image"></i> Homepage Banners
            </a>
            <a href="{{ route('admin.why-join.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.why-join.*') ? 'active' : '' }}">
                <i class="bi bi-heart"></i> Why Join Section
            </a>
            <a href="{{ route('admin.testimonials.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
                <i class="bi bi-chat-quote"></i> Testimonials
            </a>
            <a href="{{ route('admin.navigation.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.navigation.*') ? 'active' : '' }}">
                <i class="bi bi-list"></i> Navigation Menu
            </a>
            <a href="{{ route('admin.faq.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}">
                <i class="bi bi-question-circle"></i> FAQ
            </a>
            <a href="{{ route('admin.pages.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Custom Pages
            </a>
            @if (auth()->user()->isSuperAdmin())
                <div class="dashboard-nav-heading">Administration</div>
                <a href="{{ route('admin.users.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> User Management
                </a>
                <a href="{{ route('admin.roles.index') }}" class="dashboard-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Admin Roles &amp; Permissions
                </a>
            @endif
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
