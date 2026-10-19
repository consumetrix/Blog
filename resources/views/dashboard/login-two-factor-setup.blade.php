<x-layouts.dashboard title="Set up your authenticator" :bare="true">
    <div class="dash-login">
        @include('dashboard.partials.login-brand')

        <div class="dash-login__panel">
            <div class="dash-login__card">
                <h1>Set up two-step sign-in</h1>
                <p class="dash-sub">Every dashboard account signs in with a code from an authenticator app as well as the password. This takes a minute, once.</p>

                <ol class="dash-steps">
                    <li>Install an authenticator app on your phone: Google Authenticator, Microsoft Authenticator or Authy.</li>
                    <li>In the app, add an account and scan this code.</li>
                </ol>

                <div class="dash-qr" role="img" aria-label="QR code for {{ $email }}">{!! $qr !!}</div>

                <details class="dash-qr__manual">
                    <summary>Can’t scan it? Type this key instead</summary>
                    <p><code class="dash-qr__key">{{ $secret }}</code></p>
                    <p class="dash-hint">Account: {{ $email }} · Time-based</p>
                </details>

                <form class="form dash-login__form" method="POST" action="{{ route('login.two-factor.confirm') }}" novalidate>
                    @csrf
                    <div class="dash-field">
                        <label class="dash-label" for="code">3. Enter the 6-digit code the app shows</label>
                        <input class="input dash-code" type="text" id="code" name="code" inputmode="numeric" maxlength="7" autocomplete="one-time-code" required placeholder="123456"
                            @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
                        @error('code')<p class="field__error" id="code-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="dash-btn dash-btn--primary dash-btn--block">Turn on and sign in</button>
                </form>

                <p class="dash-login__resend"><a class="dash-link" href="{{ route('login') }}">Use a different account</a></p>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
