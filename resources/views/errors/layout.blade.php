<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Error') &middot; Sinaglahi</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-bg: #faf9f7;
            --color-text: #201d1a;
            --color-muted: #6b6560;
            --color-primary: #141414;
            --color-accent: #f2c230;
            --color-accent-dark: #8a6800;
            --font-display: "Playfair Display", Georgia, "Times New Roman", serif;
            --font-body: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg);
            color: var(--color-text);
            font-family: var(--font-body);
            padding: 2rem 1.5rem;
        }
        .error-card {
            max-width: 480px;
            text-align: center;
        }
        .error-code {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 5rem;
            line-height: 1;
            color: var(--color-primary);
            margin: 0 0 0.5rem;
        }
        .error-accent {
            display: inline-block;
            width: 56px;
            height: 4px;
            background: var(--color-accent);
            border-radius: 2px;
            margin-bottom: 1.25rem;
        }
        .error-title {
            font-size: 1.35rem;
            font-weight: 600;
            margin: 0 0 0.6rem;
        }
        .error-message {
            color: var(--color-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0 0 2rem;
        }
        .error-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-home {
            display: inline-block;
            padding: 0.65rem 1.5rem;
            background: var(--color-accent);
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.15s ease;
        }
        .btn-home:hover { background: var(--color-accent-dark); color: #fff; }
        .btn-back {
            display: inline-block;
            padding: 0.65rem 1.5rem;
            background: transparent;
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #d8d3c8;
            border-radius: 6px;
            transition: border-color 0.15s ease;
        }
        .btn-back:hover { border-color: var(--color-primary); }
        .brand-mark {
            display: block;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--color-muted);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <span class="brand-mark">Sinaglahi</span>
        <p class="error-code">@yield('code')</p>
        <span class="error-accent"></span>
        <h1 class="error-title">@yield('heading')</h1>
        <p class="error-message">@yield('message')</p>
        <div class="error-actions">
            @php
                $homeUrl = url('/');
                $homeLabel = 'Return to Home';
                try {
                    if (auth()->check()) {
                        $user = auth()->user();
                        if ($user->isSuperAdmin() || $user->isAdmin()) {
                            $homeUrl = route('admin.dashboard');
                            $homeLabel = 'Return to Dashboard';
                        } elseif ($user->isArtist()) {
                            $homeUrl = route('artist.dashboard');
                            $homeLabel = 'Return to Dashboard';
                        }
                    }
                } catch (\Throwable $e) {
                    // Auth/DB may be unavailable on a 500 - fall back to the public home link.
                }
            @endphp
            <a href="{{ $homeUrl }}" class="btn-home">{{ $homeLabel }}</a>
            @hasSection('secondary-action')
                @yield('secondary-action')
            @endif
        </div>
    </div>
</body>
</html>
