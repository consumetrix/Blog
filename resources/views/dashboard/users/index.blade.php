<x-layouts.dashboard title="Users">
    <header class="dash-head">
        <div>
            <p class="dash-eyebrow">Admin</p>
            <h1>Users</h1>
            <p class="dash-sub">Who can log in to the dashboard, and what they can do.</p>
        </div>
        <a class="dash-btn dash-btn--primary" href="{{ route('dashboard.users.create') }}"><x-dash.icon name="plus" /> Add user</a>
    </header>

    @if ($errors->any())
        <div class="dash-callout dash-callout--error" role="alert">
            <x-dash.icon name="alert" />
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <section class="dash-roles" aria-label="What each role can do">
        @foreach (\App\Models\User::ROLES as $key => $label)
            <div class="dash-role"><span class="dash-pill dash-pill--role-{{ $key }}">{{ $label }}</span><p>{{ \App\Models\User::ROLE_HINTS[$key] }}</p></div>
        @endforeach
    </section>

    <ul class="dash-pages">
        @foreach ($users as $user)
            @php($initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode(''))
            <li class="dash-page">
                <span class="dash-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
                <div class="dash-page__body">
                    <h2><a href="{{ route('dashboard.users.edit', $user) }}">{{ $user->name }}</a> @if ($user->is(auth()->user()))<span class="dash-page__you">(you)</span>@endif</h2>
                    <div class="dash-page__meta">
                        <span class="dash-page__url">{{ $user->email }}</span>
                        <span class="dash-pill dash-pill--role-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                    </div>
                </div>
                <div class="dash-page__actions">
                    <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('dashboard.users.edit', $user) }}"><x-dash.icon name="edit" /> Edit</a>
                </div>
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
