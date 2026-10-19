@props(['index' => null])

<p {{ $attributes->merge(['class' => 'label']) }}>
    @if ($index)<span class="label__index">{{ $index }}</span><span aria-hidden="true">·</span>@endif
    <span>{{ $slot }}</span>
</p>
