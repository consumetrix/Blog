<x-layouts.dashboard title="Settings">
    <form class="form" method="POST" action="{{ route('dashboard.settings.update') }}">
        @csrf
        @method('PUT')

        <header class="dash-head">
            <div>
                <p class="dash-eyebrow">Admin</p>
                <h1>Settings</h1>
                <p class="dash-sub">Changes here apply to every visitor straight away.</p>
            </div>
            <button type="submit" class="dash-btn dash-btn--primary"><x-dash.icon name="check" /> Save settings</button>
        </header>

        @if ($underConstruction)
            <div class="dash-callout" role="status">
                <x-dash.icon name="alert" />
                <p>The blog is <strong>under construction</strong>: visitors see a holding page. You can still see the blog because you are signed in.</p>
            </div>
        @endif

        <section class="dash-card dash-narrow" aria-labelledby="maintenance-title">
            <h2 class="dash-card__title" id="maintenance-title">Maintenance</h2>
            <label class="dash-check">
                <input type="checkbox" name="under_construction" value="1" @checked(old('under_construction', $underConstruction))>
                Under construction
            </label>
            <p class="dash-hint">While on, visitors see a holding page (HTTP 503). Signed-in users and the dashboard are not affected.</p>
        </section>
    </form>
</x-layouts.dashboard>
