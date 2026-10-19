@props(['label' => null, 'index' => null, 'lead' => null])

<section {{ $attributes->merge(['class' => 'page-hero surface-deep bg-grid']) }}>
    <div class="container page-hero__inner">
        {{ $before ?? '' }}
        @if ($label)
            <x-section-label :index="$index">{{ $label }}</x-section-label>
        @endif
        <h1>{{ $slot }}</h1>
        @if ($lead)
            <p class="lead">{{ $lead }}</p>
        @endif
        {{ $after ?? '' }}
    </div>
</section>
