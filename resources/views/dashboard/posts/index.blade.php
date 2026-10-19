@php
    $stateLabels = ['live' => 'Live', 'scheduled' => 'Scheduled', 'draft' => 'Draft'];
@endphp

<x-layouts.dashboard title="Posts">
    <header class="dash-head">
        <div>
            <p class="dash-eyebrow">Blog</p>
            <h1>Posts</h1>
            <p class="dash-sub">Write, schedule and publish articles for the blog.</p>
        </div>
        @can('edit-content')
            <a class="dash-btn dash-btn--primary" href="{{ route('dashboard.posts.create') }}"><x-dash.icon name="plus" /> New post</a>
        @endcan
    </header>

    <section class="dash-stats" aria-label="Summary">
        <div class="dash-stat dash-stat--live">
            <span class="dash-stat__label">Live on the site</span>
            <strong class="dash-stat__value">{{ $stats['live'] }}</strong>
            <span class="dash-stat__hint">Published and visible</span>
        </div>
        <div class="dash-stat dash-stat--scheduled">
            <span class="dash-stat__label">Scheduled</span>
            <strong class="dash-stat__value">{{ $stats['scheduled'] }}</strong>
            <span class="dash-stat__hint">Go live on their date</span>
        </div>
        <div class="dash-stat dash-stat--draft">
            <span class="dash-stat__label">Drafts</span>
            <strong class="dash-stat__value">{{ $stats['drafts'] }}</strong>
            <span class="dash-stat__hint">Only visible here</span>
        </div>
    </section>

    <nav class="dash-tabs" aria-label="Filter by category">
        <a href="{{ route('dashboard.posts.index') }}" @if (! $category) aria-current="page" @endif>All <span>{{ $counts->sum() }}</span></a>
        @foreach ($categories as $item)
            <a href="{{ route('dashboard.posts.index', ['category' => $item->slug]) }}" @if ($category?->is($item)) aria-current="page" @endif><span class="cat-dot" style="background: {{ $item->dot() }}"></span> {{ $item->name }} <span>{{ $counts[$item->id] ?? 0 }}</span></a>
        @endforeach
        @if ($uncategorised)
            <span class="dash-tabs__note" title="Posts with no category">No category <span>{{ $uncategorised }}</span></span>
        @endif
    </nav>

    @if ($posts->isEmpty())
        <div class="dash-empty">
            <span class="dash-empty__icon"><x-dash.icon name="posts" /></span>
            <h2>{{ $category ? 'Nothing in this category yet' : 'No posts yet' }}</h2>
            <p>Posts appear on the blog once they are published.</p>
            @can('edit-content')
                <a class="dash-btn dash-btn--primary" href="{{ route('dashboard.posts.create') }}"><x-dash.icon name="plus" /> Write a post</a>
            @endcan
        </div>
    @else
        <ul class="dash-posts">
            @foreach ($posts as $post)
                @php($state = $post->state())
                <li class="dash-post">
                    <a class="dash-post__thumb" href="{{ route('dashboard.posts.edit', $post) }}" tabindex="-1" aria-hidden="true">
                        @if ($post->imageUrl())
                            <img src="{{ $post->imageUrl() }}" alt="" loading="lazy">
                        @else
                            <span class="dash-post__tile"><span>{{ $post->typeLabel() }}</span></span>
                        @endif
                    </a>

                    <div class="dash-post__body">
                        <div class="dash-post__meta">
                            <span class="dash-pill dash-pill--{{ $state }}">{{ $stateLabels[$state] }}</span>
                            <span class="dash-post__type"><span class="cat-dot" style="background: {{ $post->category?->dot() ?? '#9498A0' }}"></span> {{ $post->typeLabel() }}</span>
                            @if ($post->published_at)
                                <span class="dash-post__date">
                                    @if ($state === 'scheduled')<x-dash.icon name="calendar" />@endif
                                    {{ $post->published_at->format('j M Y, H:i') }}
                                </span>
                            @endif
                        </div>
                        <h2><a href="{{ route('dashboard.posts.edit', $post) }}">{{ $post->title }}</a></h2>
                        <p>{{ \Illuminate\Support\Str::limit($post->summary(), 150) }}</p>
                        @unless ($post->image_path)
                            <span class="dash-post__flag"><x-dash.icon name="image" /> No image yet</span>
                        @endunless
                    </div>

                    <div class="dash-post__actions">
                        @if ($state === 'live')
                            <a class="dash-iconbtn dash-iconbtn--light" href="{{ route('posts.show', $post->slug) }}" target="_blank" rel="noopener" title="View on site"><x-dash.icon name="eye" /><span class="visually-hidden">View “{{ $post->title }}” on the site</span></a>
                        @endif
                        @can('edit-content')
                            <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('dashboard.posts.edit', $post) }}"><x-dash.icon name="edit" /> Edit</a>
                        @endcan
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.dashboard>
