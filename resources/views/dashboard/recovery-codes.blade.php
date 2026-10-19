<x-layouts.dashboard title="Your recovery codes">
    <div class="dash-actionbar">
        <div class="dash-actionbar__title">
            <h1>Save your recovery codes</h1>
        </div>
    </div>

    <div class="dash-card dash-narrow">
        <p>Two-step sign-in is on. If you ever lose your phone, sign in with one of these codes instead of an app code. <strong>Each works once, and they are shown only now.</strong> Keep them somewhere safe, such as a password manager.</p>

        <ul class="dash-recovery" aria-label="Recovery codes">
            @foreach ($codes as $code)
                <li><code>{{ $code }}</code></li>
            @endforeach
        </ul>

        <p class="dash-hint">Used them all, or lost them? An admin can reset your authenticator under Users; you then set it up again at your next sign-in.</p>

        <a class="dash-btn dash-btn--primary" href="{{ $next }}"><x-dash.icon name="check" /> I’ve saved them</a>
    </div>
</x-layouts.dashboard>
