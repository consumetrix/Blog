@props(['title' => 'Dashboard', 'bare' => false])

@php
    $user = auth()->user();
    $initials = $user ? collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') : '';
@endphp

<!DOCTYPE html>
<html lang="en-GB" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.replace('no-js', 'js');</script>
    <title>{{ $title }} — {{ $site['name'] }} dashboard</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0A162E">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    @include('partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="dash">
    {{-- Sign-in screens are always drawn without the dashboard shell (also in the Site settings preview). --}}
    @if (auth()->check() && ! $bare)
        <div class="dash-shell">
            <aside class="dash-side surface-deep">
                <div class="dash-side__brand">
                    <x-logo variant="dark" :width="148" :href="route('dashboard')" />
                    <span class="dash-side__tag">Blog</span>
                </div>

                <nav class="dash-side__nav" aria-label="Dashboard">
                    <span class="dash-side__group">Blog</span>
                    <a href="{{ route('dashboard.posts.index') }}" @if (request()->routeIs('dashboard', 'dashboard.posts.*')) aria-current="page" @endif>
                        <x-dash.icon name="posts" /><span>Posts</span><span class="dash-side__count">{{ $navCounts['posts'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('dashboard.media.index') }}" @if (request()->routeIs('dashboard.media.*')) aria-current="page" @endif>
                        <x-dash.icon name="media" /><span>Media</span><span class="dash-side__count">{{ $navCounts['media'] ?? 0 }}</span>
                    </a>
                    @can('manage-settings')
                        <a href="{{ route('dashboard.settings') }}" @if (request()->routeIs('dashboard.settings*')) aria-current="page" @endif>
                            <x-dash.icon name="settings" /><span>Settings</span>
                        </a>
                    @endcan
                    @can('manage-users')
                        <span class="dash-side__group">Admin</span>
                        <a href="{{ route('dashboard.users.index') }}" @if (request()->routeIs('dashboard.users.*')) aria-current="page" @endif>
                            <x-dash.icon name="users" /><span>Users</span><span class="dash-side__count">{{ $navCounts['users'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('dashboard.activity') }}" @if (request()->routeIs('dashboard.activity')) aria-current="page" @endif>
                            <x-dash.icon name="activity" /><span>Activity</span>
                        </a>
                    @endcan
                    <span class="dash-side__group">Public</span>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener">
                        <x-dash.icon name="external" /><span>View blog</span>
                    </a>
                </nav>

                <div class="dash-side__user">
                    <span class="dash-avatar" aria-hidden="true">{{ $initials ?: 'C' }}</span>
                    <span class="dash-side__who">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->roleLabel() }} · {{ $user->email }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dash-iconbtn" title="Log out"><x-dash.icon name="logout" /><span class="visually-hidden">Log out</span></button>
                    </form>
                </div>
            </aside>

            <main class="dash-main surface-paper" id="main">
                @if (session('status'))
                    <div class="dash-toast" role="status"><x-dash.icon name="check" /><span>{{ session('status') }}</span></div>
                @endif
                {{ $slot }}
            </main>
        </div>
    @else
        <main id="main">{{ $slot }}</main>
    @endif
</body>
</html>
