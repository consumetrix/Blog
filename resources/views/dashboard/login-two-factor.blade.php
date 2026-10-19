<x-layouts.dashboard title="Enter your code" :bare="true">
    <div class="dash-login">
        @include('dashboard.partials.login-brand')

        <div class="dash-login__panel">
            <div class="dash-login__card">
                <h1>Enter your code</h1>
                <p class="dash-sub">Open your authenticator app and type the 6-digit code for {{ $site['company'] }}.</p>

                <form class="form dash-login__form" method="POST" action="{{ route('login.two-factor.verify') }}" novalidate>
                    @csrf
                    <div class="dash-field">
                        <label class="dash-label" for="code">Code</label>
                        <input class="input dash-code" type="text" id="code" name="code" inputmode="numeric" maxlength="11" autocomplete="one-time-code" required autofocus placeholder="123456"
                            @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
                        @error('code')<p class="field__error" id="code-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="dash-btn dash-btn--primary dash-btn--block">Sign in</button>
                </form>

                <p class="dash-login__resend">Lost your phone? Type one of your recovery codes (like <code>abcde-fghij</code>) in the box instead.</p>
                <p class="dash-login__resend"><a class="dash-link" href="{{ route('login') }}">Use a different account</a></p>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
