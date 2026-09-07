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
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />
</head>
<body class="dashboard-body">
    <header class="dashboard-topbar">
        <div class="dashboard-topbar-inner">
            <a class="dashboard-brand" href="{{ route('home') }}">Sinaglahi</a>
            <span class="dashboard-topbar-title">Artist Dashboard</span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white small">{{ auth()->user()->artist->ArtistName ?? auth()->user()->Email }}</span>
                <form action="{{ route('account.logout') }}" method="post" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Log Out</button>
                </form>
            </div>
        </div>
    </header>

    <div class="dashboard-shell">
        <nav class="dashboard-sidebar">
            <a href="{{ route('artist.dashboard') }}" class="dashboard-nav-link {{ request()->routeIs('artist.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            {{-- Profile editing and artwork management are ported next. --}}
        </nav>

        <main class="dashboard-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
