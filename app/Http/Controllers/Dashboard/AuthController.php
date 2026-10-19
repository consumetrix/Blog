<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Dashboard sign-in: the password, then a 6-digit code from an authenticator app (App\Support\TwoFactor).
 * Someone without the app set up scans a QR code at that step first, and is shown recovery codes
 * once. Between the two steps the person waits in the session, for a few minutes and a few tries.
 */
class AuthController extends Controller
{
    private const PENDING = 'login.pending';

    public function show(): View
    {
        return view('dashboard.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = Auth::getProvider()->retrieveByCredentials(['email' => $credentials['email']]);
        if (! $user || ! Auth::getProvider()->validateCredentials($user, $credentials)) {
            ActivityLog::record('auth.failed', mb_strtolower($credentials['email']));

            throw ValidationException::withMessages(['email' => 'Those details don’t match an account.']);
        }

        if (! TwoFactor::enabled()) {
            return $this->signIn($request, $user, $request->boolean('remember'));
        }

        $request->session()->put(self::PENDING, [
            'user' => $user->id,
            'remember' => $request->boolean('remember'),
            'expires' => now()->addMinutes((int) config('blog.two_factor.minutes'))->timestamp,
            'attempts' => 0,
        ]);

        return redirect()->route($user->hasTwoFactor() ? 'login.two-factor' : 'login.two-factor.setup');
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return $this->restart($request);
        }
        if (! $user->hasTwoFactor()) {
            return redirect()->route('login.two-factor.setup');
        }

        return view('dashboard.login-two-factor');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:40']]);
        $user = $this->pendingUser($request);
        if (! $user || ! $user->hasTwoFactor()) {
            return $this->restart($request);
        }

        $code = (string) $request->input('code');
        if (TwoFactor::verify($user->two_factor_secret, $code, $user)) {
            return $this->signIn($request, $user, $this->remember($request));
        }

        // Recovery codes look like "abcde-fghij"; app codes are digits only.
        if (preg_match('/[a-z]/i', $code) && TwoFactor::useRecoveryCode($user, $code)) {
            ActivityLog::record('auth.recovery_code_used', $user->email);
            $left = count($user->fresh()->two_factor_recovery_codes ?? []);

            return $this->signIn($request, $user, $this->remember($request))
                ->with('status', "You signed in with a recovery code. {$left} left. If you’ve lost your phone, ask an admin to reset your authenticator.");
        }

        return $this->wrongCode($request, $user);
    }

    public function showSetup(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return $this->restart($request);
        }
        if ($user->hasTwoFactor()) {
            return redirect()->route('login.two-factor');
        }

        // The secret stays in the session until a code from the app proves it was saved there.
        $secret = $request->session()->get(self::PENDING.'.secret');
        if (! $secret) {
            $secret = TwoFactor::newSecret();
            $request->session()->put(self::PENDING.'.secret', $secret);
        }

        return view('dashboard.login-two-factor-setup', [
            'qr' => TwoFactor::qrSvg($user, $secret),
            'secret' => TwoFactor::formatted($secret),
            'email' => $user->email,
        ]);
    }

    public function confirmSetup(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $this->pendingUser($request);
        $secret = $request->session()->get(self::PENDING.'.secret');
        if (! $user || ! $secret) {
            return $this->restart($request);
        }

        if (! TwoFactor::verify($secret, (string) $request->input('code'), $user)) {
            return $this->wrongCode($request, $user);
        }

        $codes = TwoFactor::newRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();
        ActivityLog::record('auth.two_factor_enabled', $user->email);

        $next = $this->signIn($request, $user, $this->remember($request))->getTargetUrl();

        return redirect()->route('dashboard.recovery-codes')->with(['recovery_codes' => $codes, 'after_codes' => $next]);
    }

    /** Shown once, straight after setup. */
    public function recoveryCodes(Request $request): View|RedirectResponse
    {
        $codes = $request->session()->get('recovery_codes');

        return $codes
            ? view('dashboard.recovery-codes', ['codes' => $codes, 'next' => $request->session()->get('after_codes', route('dashboard'))])
            : redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            ActivityLog::record('auth.logout', $user->email);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function signIn(Request $request, User $user, bool $remember): RedirectResponse
    {
        $request->session()->forget(self::PENDING);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        ActivityLog::record('auth.login', $user->email);

        return redirect()->intended(route('dashboard'));
    }

    private function wrongCode(Request $request, User $user): RedirectResponse
    {
        ActivityLog::record('auth.two_factor_failed', $user->email);
        $attempts = (int) $request->session()->get(self::PENDING.'.attempts') + 1;

        if ($attempts >= (int) config('blog.two_factor.attempts')) {
            $request->session()->forget(self::PENDING);

            return redirect()->route('login')->withErrors(['email' => 'Too many wrong codes. Please log in again.']);
        }
        $request->session()->put(self::PENDING.'.attempts', $attempts);

        throw ValidationException::withMessages(['code' => 'That code isn’t right. Use the code showing in the app now (it changes every 30 seconds).']);
    }

    /** The person between the two steps, while their time lasts. */
    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get(self::PENDING);
        if (! $pending || now()->timestamp > ($pending['expires'] ?? 0)) {
            return null;
        }

        return User::find($pending['user']);
    }

    private function remember(Request $request): bool
    {
        return (bool) $request->session()->get(self::PENDING.'.remember');
    }

    private function restart(Request $request): RedirectResponse
    {
        $expired = $request->session()->has(self::PENDING);
        $request->session()->forget(self::PENDING);
        $redirect = redirect()->route('login');

        return $expired ? $redirect->withErrors(['email' => 'That took too long. Please log in again.']) : $redirect;
    }
}
