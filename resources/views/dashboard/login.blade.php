<x-layouts.dashboard title="Log in" :bare="true">
    <div class="dash-login">
        @include('dashboard.partials.login-brand')

        <div class="dash-login__panel">
            <div class="dash-login__card">
                <h1>Log in</h1>
                <p class="dash-sub">Use your dashboard account.</p>

                <form class="form dash-login__form" method="POST" action="{{ route('login.attempt') }}" novalidate>
                    @csrf
                    <div class="dash-field">
                        <label class="dash-label" for="email">Email</label>
                        <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus placeholder="you@example.com"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        @error('email')<p class="field__error" id="email-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="dash-field">
                        <label class="dash-label" for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" autocomplete="current-password" required
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        @error('password')<p class="field__error" id="password-error">{{ $message }}</p>@enderror
                    </div>
                    <label class="dash-check"><input type="checkbox" name="remember" value="1"> Keep me logged in on this device</label>
                    <button type="submit" class="dash-btn dash-btn--primary dash-btn--block">Log in</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
