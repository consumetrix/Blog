@php
    $editing = $user->exists;
    $role = old('role', $user->role);
@endphp

<x-layouts.dashboard :title="$editing ? 'Edit user' : 'Add user'">
    <form class="form" method="POST" action="{{ $editing ? route('dashboard.users.update', $user) : route('dashboard.users.store') }}" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="dash-actionbar">
            <a class="dash-back" href="{{ route('dashboard.users.index') }}"><x-dash.icon name="back" /> Users</a>
            <div class="dash-actionbar__title"><h1>{{ $editing ? $user->name : 'Add user' }}</h1></div>
            <div class="dash-actionbar__actions">
                <button type="submit" class="dash-btn dash-btn--primary"><x-dash.icon name="check" /> {{ $editing ? 'Save' : 'Create account' }}</button>
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
            <div class="dash-row">
                <div class="dash-field">
                    <label class="dash-label" for="name">Name</label>
                    <input class="input" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" autocomplete="off">
                </div>
                <div class="dash-field">
                    <label class="dash-label" for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="190" autocomplete="off">
                </div>
            </div>

            <fieldset class="dash-field">
                <legend class="dash-label">Role</legend>
                <div class="dash-role-pick">
                    @foreach (\App\Models\User::ROLES as $key => $label)
                        <label>
                            <input type="radio" name="role" value="{{ $key }}" @checked($role === $key)>
                            <span><strong>{{ $label }}</strong>{{ \App\Models\User::ROLE_HINTS[$key] }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="dash-row">
                <div class="dash-field">
                    <label class="dash-label" for="password">{{ $editing ? 'New password' : 'Password' }} @if ($editing)<span>leave empty to keep</span>@endif</label>
                    <input class="input" type="password" id="password" name="password" minlength="12" autocomplete="new-password" @unless ($editing) required @endunless>
                    <p class="dash-hint">At least 12 characters. Share it with the person privately.</p>
                </div>
                <div class="dash-field">
                    <label class="dash-label" for="password_confirmation">Repeat the password</label>
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        <form class="dash-danger dash-narrow" method="POST" action="{{ route('dashboard.users.two-factor.reset', $user) }}" onsubmit="return confirm('Reset this authenticator app? They set it up again at their next sign-in.')">
            @csrf
            <div>
                <h2>Authenticator app</h2>
                @if ($user->hasTwoFactor())
                    <p>Set up {{ $user->two_factor_confirmed_at->diffForHumans() }}, {{ count($user->two_factor_recovery_codes ?? []) }} recovery codes left. Reset it if they lost their phone: they scan a new code at their next sign-in.</p>
                @else
                    <p>Not set up yet. They will be asked to set it up at their next sign-in.</p>
                @endif
            </div>
            @if ($user->hasTwoFactor())
                <button type="submit" class="dash-btn dash-btn--ghost">Reset authenticator</button>
            @endif
        </form>
    @endif

    @if ($editing && ! $user->is(auth()->user()))
        <form class="dash-danger dash-narrow" method="POST" action="{{ route('dashboard.users.destroy', $user) }}" onsubmit="return confirm('Delete this account? They will no longer be able to log in.')">
            @csrf
            @method('DELETE')
            <div>
                <h2>Delete this account</h2>
                <p>{{ $user->email }} will no longer be able to log in. Their past edits stay in the history.</p>
            </div>
            <button type="submit" class="dash-btn dash-btn--danger"><x-dash.icon name="trash" /> Delete account</button>
        </form>
    @endif
</x-layouts.dashboard>
