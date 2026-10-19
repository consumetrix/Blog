<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('blog:admin {email : Login email for the dashboard} {--name= : Display name} {--reset-2fa : Only reset the authenticator app of an existing account (lost phone)}')]
#[Description('Create an admin dashboard account, or reset the password of an existing one (and make it an admin).')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        if ($this->option('reset-2fa')) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->error("There is no account for {$email}.");

                return self::FAILURE;
            }
            TwoFactor::reset($user);
            ActivityLog::record('auth.two_factor_reset', $user->email);
            $this->info("Authenticator reset for {$email}. They set it up again at their next sign-in.");

            return self::SUCCESS;
        }

        $password = $this->secret('Password (at least 12 characters)');
        if (mb_strlen((string) $password) < 12) {
            $this->error('The password must be at least 12 characters.');

            return self::FAILURE;
        }
        if ($this->secret('Repeat the password') !== $password) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;
        $user->name = $this->option('name') ?: ($user->name ?? "Admin");
        $user->password = $password;
        $user->role = 'admin';
        $user->save();

        $this->info($existed ? "Password updated for {$email}." : "Dashboard account created for {$email}.");
        $this->line('Log in at '.route('login'));

        return self::SUCCESS;
    }
}
