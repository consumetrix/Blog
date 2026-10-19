<?php

namespace Tests;

use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PragmaRX\Google2FA\Google2FA;

abstract class TestCase extends BaseTestCase
{
    /** Gives the account a confirmed authenticator app and returns its secret. */
    protected function withAuthenticator(User $user): string
    {
        $secret = TwoFactor::newSecret();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_recovery_codes' => TwoFactor::newRecoveryCodes(), 'two_factor_confirmed_at' => now()])->save();

        return $secret;
    }

    /** The code an authenticator app would show now. */
    protected function appCode(string $secret): string
    {
        return (new Google2FA)->getCurrentOtp($secret);
    }

    /** Signs in through the real forms: password, then the app's code (setting the app up first if needed). */
    protected function signInThroughForms(User $user, string $password): void
    {
        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => $password])->assertRedirect();

        if ($user->fresh()->hasTwoFactor()) {
            $this->post(route('login.two-factor.verify'), ['code' => $this->appCode($user->fresh()->two_factor_secret)])->assertRedirect();
        } else {
            $this->get(route('login.two-factor.setup'))->assertOk();
            $this->post(route('login.two-factor.confirm'), ['code' => $this->appCode(session('login.pending.secret'))])->assertRedirect(route('dashboard.recovery-codes'));
        }
    }
}
