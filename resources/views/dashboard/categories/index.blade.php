<x-layouts.dashboard title="Categories">
    <header class="dash-head">
        <div>
            <p class="dash-eyebrow">Blog</p>
            <h1>Categories</h1>
            <p class="dash-sub">Group posts and let readers filter the blog. Deleting a category keeps its posts, with no category.</p>
        </div>
    </header>

    @if ($errors->any())
        <div class="dash-callout dash-callout--error" role="alert" data-error-summary tabindex="-1">
            <x-dash.icon name="alert" />
            <div>
                <h2>Please check the form.</h2>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if ($categories->isEmpty())
        <div class="dash-empty">
            <span class="dash-empty__icon"><x-dash.icon name="stack" /></span>
            <h2>No categories yet</h2>
            <p>Add one below. Posts can be written without a category, too.</p>
        </div>
    @else
        <ul class="dash-pages">
            @foreach ($categories as $category)
                <li class="dash-page">
                    <span class="dash-avatar" aria-hidden="true" style="background: {{ $category->dot() }}">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                    <div class="dash-page__body">
                        <h2>
                            @can('edit-content')
                                <a href="{{ route('dashboard.categories.edit', $category) }}">{{ $category->name }}</a>
                            @else
                                {{ $category->name }}
                            @endcan
                        </h2>
                        <div class="dash-page__meta">
                            <span class="dash-page__url">?category={{ $category->slug }}</span>
                            <span class="dash-pill">{{ $category->posts_count }} {{ \Illuminate\Support\Str::plural('post', $category->posts_count) }}</span>
                            @if ($category->description)
                                <span>{{ \Illuminate\Support\Str::limit($category->description, 90) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="dash-page__actions">
                        <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('dashboard.posts.index', ['category' => $category->slug]) }}">Posts</a>
                        @can('edit-content')
                            <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('dashboard.categories.edit', $category) }}"><x-dash.icon name="edit" /> Edit</a>
                        @endcan
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @can('edit-content')
        <form class="form dash-card dash-narrow" method="POST" action="{{ route('dashboard.categories.store') }}" style="margin-top: var(--space-xl)" novalidate>
            @csrf
            <h2 class="dash-card__title">Add a category</h2>
            @include('dashboard.categories.fields', ['category' => new \App\Models\Category, 'prefix' => 'new'])
            <div>
                <button type="submit" class="dash-btn dash-btn--primary"><x-dash.icon name="plus" /> Add category</button>
            </div>
        </form>
    @endcan
</x-layouts.dashboard>
