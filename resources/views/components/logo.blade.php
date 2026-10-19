@props([
    'variant' => 'white',
    'width' => 150,
    'href' => null,
])

@php
    $files = [
        'white' => ['base' => 'images/brand/consumetrix-wordmark-white', 'w' => 500, 'h' => 107],
        'colour' => ['base' => 'images/brand/consumetrix-logo', 'w' => 1008, 'h' => 380],
        'dark' => ['base' => 'images/brand/consumetrix-logo-dark', 'w' => 480, 'h' => 200],
    ];
    $file = $files[$variant];
    $width = max(120, (int) $width);
    $classes = 'logo logo--'.$variant;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} style="--logo-w: {{ $width }}px" aria-label="Consumetrix home">
@else
    <span {{ $attributes->merge(['class' => $classes]) }} style="--logo-w: {{ $width }}px">
@endif
        <picture>
            <source srcset="{{ asset($file['base'].'.webp') }}" type="image/webp">
            <img src="{{ asset($file['base'].'.png') }}" width="{{ $file['w'] }}" height="{{ $file['h'] }}" alt="{{ $href ? '' : 'Consumetrix' }}" decoding="async">
        </picture>
@if ($href)
    </a>
@else
    </span>
@endif
