<x-layouts.app :title="$post->title" :description="\Illuminate\Support\Str::limit($post->summary(300), 155)" :og-image="$post->imageUrl()" og-type="article" :article="$post">
    <x-page-hero :label="mb_strtoupper($post->typeLabel())">
        <x-slot:before>
            <a class="back-link" href="{{ route('home') }}"><x-arrow /> Blog</a>
        </x-slot:before>
        {{ $post->title }}
        <x-slot:after>
            @if ($post->published_at)
                <p class="mono muted"><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time></p>
            @endif
        </x-slot:after>
    </x-page-hero>

    <section class="section surface-paper">
        <div class="container post-reader">
            <div class="post-reader__main">
                {{-- The image sits at the start of the text: the text runs beside it, then on underneath. --}}
                <div class="reader__body">
                    <x-post-media class="post-reader__media" :post="$post" number="—" sizes="(min-width: 900px) 26rem, 100vw" :priority="true" />
                    {!! $post->bodyHtml() !!}
                </div>

                <aside class="panel post-reader__cta" aria-label="Talk to us">
                    <div class="stack">
                        <p class="muted">Working through this in your organisation?</p>
                        <x-button :href="$site['contact_url']" size="sm">Talk to us</x-button>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section" aria-labelledby="related-title">
            <div class="container">
                <x-section-label>More {{ $post->typeLabel() }}</x-section-label>
                <h2 id="related-title" class="visually-hidden">Related posts</h2>
                @foreach ($related as $item)
                    <article class="post-row">
                        <a class="post-row__media" href="{{ route('posts.show', $item->slug) }}" tabindex="-1" aria-hidden="true">
                            <x-post-media :post="$item" number="—" sizes="(min-width: 800px) 32vw, 100vw" />
                        </a>
                        <div class="post-row__body">
                            <span class="mono muted">{{ mb_strtoupper($item->typeLabel()) }}</span>
                            <h3><a href="{{ route('posts.show', $item->slug) }}">{{ $item->title }}</a></h3>
                            <p>{{ $item->summary() }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
