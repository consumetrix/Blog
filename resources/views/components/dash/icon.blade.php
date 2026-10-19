@props(['name'])

@php
    $paths = [
        'posts' => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M10 12h5M10 16h5"/>',
        'clients' => '<path d="M4 21V8l8-5 8 5v13"/><path d="M9 21v-6h6v6M9 10.5h.01M15 10.5h.01"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/>',
        'logout' => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'image' => '<rect x="3" y="5" width="18" height="14"/><path d="M3 16l5-5 4 4 3-3 6 6"/><circle cx="15.5" cy="9.5" r="1.5"/>',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>',
        'download' => '<path d="M12 4v12M7 11l5 5 5-5M4 20h16"/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'edit' => '<path d="M4 20h4L20 8l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'back' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'alert' => '<path d="M12 4l9 16H3z"/><path d="M12 10v4M12 17h.01"/>',
        'pages' => '<rect x="4" y="3" width="16" height="18"/><path d="M4 8h16M8 12h8M8 16h5"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.3-5.5 6.5-5.5s5.7 1.9 6.5 5.5M16 4.8a3.5 3.5 0 0 1 0 6.4M18.5 14.8c1.5.9 2.5 2.6 3 5.2"/>',
        'activity' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12"/><path d="M16 8V4H4v12h4"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 3"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'media' => '<rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><path d="M13 19l3-3 2 2 3-3"/>',
        'settings' => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l5 5"/>',
        'up' => '<path d="M6 15l6-6 6 6"/>',
        'down' => '<path d="M6 9l6 6 6-6"/>',
        'stack' => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'dash-icon', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.6', 'stroke-linecap' => 'square', 'aria-hidden' => 'true', 'focusable' => 'false']) }}>{!! $paths[$name] ?? '' !!}</svg>
