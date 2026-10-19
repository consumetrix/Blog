@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'noindex' => false,
    'article' => null,
])

@php
    $pageTitle = $title ? $title.' — '.$site['name'] : $site['name'].' — '.$site['tagline'];
    $metaDescription = $description ?? $site['tagline'];
    $canonicalUrl = $canonical ?? url()->current();
    $shareImage = $ogImage ?? asset('images/brand/og-default.jpg');
    $indexable = ! $noindex && app()->environment('production');
@endphp

<!DOCTYPE html>
<html lang="en-GB" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <script>document.documentElement.classList.replace('no-js', 'js');</script>

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="theme-color" content="#0A162E">
    <meta name="robots" content="{{ $indexable ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' }}">

    <meta property="og:site_name" content="{{ $site['name'] }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:locale" content="en_GB">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $site['name'] }}" href="{{ route('feed') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preload" href="{{ asset('fonts/merriweather-latin-700-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($article)
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $metaDescription,
            'image' => $shareImage,
            'datePublished' => $article->published_at?->toAtomString(),
            'dateModified' => $article->updated_at->toAtomString(),
            'articleSection' => $article->typeLabel(),
            'mainEntityOfPage' => $canonicalUrl,
            'publisher' => ['@type' => 'Organization', 'name' => $site['company'], 'url' => $site['main_site']],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <x-site-header />

    <main id="main" tabindex="-1">
        {{ $slot }}
    </main>

    <x-site-footer />
</body>
</html>
