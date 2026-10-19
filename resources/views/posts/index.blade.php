<x-layouts.app :description="$site['tagline']">
    <x-page-hero label="BLOG" :lead="$site['tagline']">Blog</x-page-hero>

    <section class="section" aria-label="Posts">
        <div class="container">
            <ul class="filter-tags" aria-label="Filter by category">
                <li><a href="{{ route('home') }}" @if (! $category) aria-current="true" @endif>All <span class="filter-tags__count">{{ $total }}</span></a></li>
                @foreach ($categories as $item)
                    <li>
                        <a href="{{ route('home', ['category' => $item->slug]) }}" @if ($category?->is($item)) aria-current="true" @endif>
                            <span class="cat-dot" style="background: {{ $item->dot() }}"></span> {{ $item->name }} <span class="filter-tags__count">{{ $counts[$item->id] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($category?->description)
                <p class="muted" style="margin-top: var(--space-lg); max-width: 40em">{{ $category->description }}</p>
            @endif

            @forelse ($posts as $post)
                @php($number = str_pad($posts->firstItem() + $loop->index, 2, '0', STR_PAD_LEFT))
                <article class="post-row" data-reveal>
                    <a class="post-row__media" href="{{ route('posts.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
                        <x-post-media :post="$post" :number="$number" sizes="(min-width: 800px) 32vw, 100vw" />
                    </a>
                    <div class="post-row__body">
                        <span class="mono muted">{{ $number }} — {{ mb_strtoupper($post->typeLabel()) }}</span>
                        <h3><a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a></h3>
                        <p>{{ $post->summary() }}</p>
                        <span class="post-row__meta mono">
                            @if ($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j M Y') }}</time>
                            @endif
                            <span aria-hidden="true">Read <x-arrow /></span>
                        </span>
                    </div>
                </article>
            @empty
                <p class="muted" style="margin-top: var(--space-xl)">{{ $total ? 'No posts in this category yet.' : 'No posts have been published yet.' }}</p>
            @endforelse

            @if ($posts->hasPages())
                <nav aria-label="Pages" style="margin-top: var(--space-xl)">
                    <ul style="list-style: none; display: flex; gap: var(--space-sm)">
                        @if ($posts->previousPageUrl())
                            <li><x-button :href="$posts->previousPageUrl()" variant="secondary" size="sm" :arrow="false" rel="prev">Newer</x-button></li>
                        @endif
                        @if ($posts->nextPageUrl())
                            <li><x-button :href="$posts->nextPageUrl()" variant="secondary" size="sm" :arrow="false" rel="next">Older</x-button></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </section>
</x-layouts.app>
