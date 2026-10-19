@php
    $site = [
        'name' => config('blog.name'),
        'company' => config('blog.company'),
        'main_site' => config('blog.main_site'),
    ];
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $site['name'] }} — Coming Soon</title>
    <meta name="description" content="We're preparing something great. Check back soon.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0A162E">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preload" href="{{ asset('fonts/merriweather-latin-700-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @include('partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .uc-page {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            min-height: 100dvh;
            padding: var(--space-2xl) var(--gutter);
            text-align: center;
            overflow: hidden;
        }

        /* Animated gradient orbs */
        .uc-page::before,
        .uc-page::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
            animation: uc-float 12s ease-in-out infinite alternate;
            pointer-events: none;
        }
        .uc-page::before {
            width: min(600px, 80vw);
            height: min(600px, 80vw);
            top: -15%;
            left: -10%;
            background: radial-gradient(circle, var(--teal-glow) 0%, transparent 70%);
        }
        .uc-page::after {
            width: min(500px, 70vw);
            height: min(500px, 70vw);
            bottom: -15%;
            right: -10%;
            background: radial-gradient(circle, var(--gold-glow) 0%, transparent 70%);
            animation-delay: -6s;
        }

        @keyframes uc-float {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -20px) scale(1.12); }
        }

        .uc-content {
            position: relative;
            z-index: 1;
            max-width: 38em;
        }

        .uc-logo {
            display: inline-block;
            margin-bottom: var(--space-3xl);
            opacity: 0;
            animation: uc-rise 0.9s var(--ease) 0.1s forwards;
        }
        .uc-logo img {
            width: clamp(160px, 30vw, 240px);
            height: auto;
        }

        .uc-rule {
            width: 64px;
            height: 2px;
            border: none;
            background: linear-gradient(90deg, var(--gold), var(--teal));
            margin: 0 auto var(--space-2xl);
            opacity: 0;
            animation: uc-rise 0.9s var(--ease) 0.25s forwards;
        }

        .uc-page h1 {
            font-size: var(--h2);
            color: var(--white);
            margin-bottom: var(--space-lg);
            opacity: 0;
            animation: uc-rise 0.9s var(--ease) 0.35s forwards;
        }

        .uc-text {
            font-size: var(--text-lg);
            color: var(--fg-muted, rgba(247, 244, 236, 0.74));
            line-height: 1.7;
            margin-bottom: var(--space-2xl);
            opacity: 0;
            animation: uc-rise 0.9s var(--ease) 0.5s forwards;
        }

        .uc-contact {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-sm) var(--space-xl);
            border: 1px solid var(--glass-border);
            border-radius: 999px;
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            color: var(--gold-light);
            font-size: var(--text-sm);
            text-decoration: none;
            transition: border-color var(--dur) var(--ease), background var(--dur) var(--ease), transform var(--dur-fast) var(--ease);
            opacity: 0;
            animation: uc-rise 0.9s var(--ease) 0.65s forwards;
        }
        .uc-contact:hover {
            border-color: var(--gold);
            background: rgba(199, 154, 69, 0.08);
            transform: translateY(-2px);
        }
        .uc-contact svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .uc-footer {
            position: relative;
            z-index: 1;
            margin-top: var(--space-4xl);
            font-size: var(--text-xs);
            color: var(--mist);
            opacity: 0;
            animation: uc-rise 0.9s var(--ease) 0.8s forwards;
        }

        @keyframes uc-rise {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="uc-page surface-deep bg-grid">
        <div class="uc-content">
            <div class="uc-logo">
                <picture>
                    <source srcset="{{ asset('images/brand/consumetrix-logo-dark.webp') }}" type="image/webp">
                    <img src="{{ asset('images/brand/consumetrix-logo-dark.png') }}" alt="{{ $site['company'] }}" width="480" height="200">
                </picture>
            </div>

            <hr class="uc-rule">

            <h1>We're building something great.</h1>

            <p class="uc-text">The blog is currently under construction. We'll be live&nbsp;soon&nbsp;— in the meantime, visit our main site.</p>

            <a class="uc-contact" href="{{ $site['main_site'] }}" rel="noopener">{{ preg_replace('#^https?://#', '', $site['main_site']) }}</a>
        </div>

        <p class="uc-footer">&copy; {{ date('Y') }} {{ $site['company'] }}</p>
    </div>
</body>
</html>
