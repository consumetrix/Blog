@props(['post', 'number', 'sizes' => '(min-width: 900px) 40vw, 100vw', 'priority' => false])

@if ($post->imageUrl())
    <figure {{ $attributes->merge(['class' => 'post-media']) }}>
        <img src="{{ $post->imageUrl() }}" alt="{{ $post->image_alt ?? '' }}" sizes="{{ $sizes }}"
            @if ($priority) fetchpriority="high" @else loading="lazy" @endif decoding="async">
    </figure>
@else
    {{-- No image supplied: a typographic tile in brand colours keeps the image-left layout. --}}
    <div {{ $attributes->merge(['class' => 'post-media post-media--tile surface-deep bg-grid']) }} aria-hidden="true">
        <span class="mono">{{ $number }}</span>
        <span class="post-media__type mono">{{ $post->typeLabel() }}</span>
    </div>
@endif
