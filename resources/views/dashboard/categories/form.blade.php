<x-layouts.dashboard title="Edit category">
    <form class="form" method="POST" action="{{ route('dashboard.categories.update', $category) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="dash-actionbar">
            <a class="dash-back" href="{{ route('dashboard.categories.index') }}"><x-dash.icon name="back" /> Categories</a>
            <div class="dash-actionbar__title"><h1>{{ $category->name }}</h1></div>
            <div class="dash-actionbar__actions">
                <button type="submit" class="dash-btn dash-btn--primary"><x-dash.icon name="check" /> Save</button>
            </div>
        </div>

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

        <div class="dash-card dash-narrow">
            @include('dashboard.categories.fields', ['prefix' => 'edit'])
            <p class="dash-hint">Changing the web address changes the link to this category, but posts are not affected.</p>
        </div>
    </form>

    <form class="dash-danger dash-narrow" method="POST" action="{{ route('dashboard.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Its posts are kept, with no category.')">
        @csrf
        @method('DELETE')
        <div>
            <h2>Delete this category</h2>
            <p>{{ $category->posts_count ? "Its {$category->posts_count} ".\Illuminate\Support\Str::plural('post', $category->posts_count).' will stay on the blog, with no category.' : 'No posts use it.' }}</p>
        </div>
        <button type="submit" class="dash-btn dash-btn--danger"><x-dash.icon name="trash" /> Delete category</button>
    </form>
</x-layouts.dashboard>
