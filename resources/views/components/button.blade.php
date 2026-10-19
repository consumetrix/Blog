@props([
    'href' => null,
    'variant' => 'primary',
    'size' => null,
    'arrow' => true,
    'type' => 'submit',
])

@php
    $classes = 'btn btn--'.$variant.($size ? ' btn--'.$size : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span>{{ $slot }}</span>
        @if ($arrow)<x-arrow class="btn__arrow" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span>{{ $slot }}</span>
        @if ($arrow)<x-arrow class="btn__arrow" />@endif
    </button>
@endif
