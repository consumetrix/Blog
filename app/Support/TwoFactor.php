<?php

namespace App\Support;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Two-factor sign-in with an authenticator app (Google Authenticator, Microsoft Authenticator,
 * Authy, 1Password…): 6-digit codes that change every 30 seconds (TOTP), plus one-time recovery
 * codes for a lost phone. The same approach as the BLI site (pragmarx/google2fa, bacon/bacon-qr-code).
 */
class TwoFactor
{
    public const RECOVERY_CODES = 8;

    public static function enabled(): bool
    {
        return (bool) config('blog.two_factor.enabled');
    }

    public static function newSecret(): string
    {
        return self::engine()->generateSecretKey(32);
    }

    /** The otpauth:// address the app reads from the QR code: the app shows "Consumetrix (you@consumetrix.biz)". */
    public static function uri(User $user, string $secret): string
    {
        return self::engine()->getQRCodeUrl(config('blog.company'), $user->email, $secret);
    }

    public static function qrSvg(User $user, string $secret): string
    {
        $svg = (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString(self::uri($user, $secret));

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /** "ABCD EFGH …" for typing the secret by hand. */
    public static function formatted(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    /**
     * Whether a 6-digit code is right for the secret: the current 30 seconds, or the one either side
     * for a slow phone clock. A code that worked once is refused for the next few minutes.
     */
    public static function verify(string $secret, string $code, User $user): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6 || ! self::engine()->verifyKey($secret, $code, 1)) {
            return false;
        }

        return Cache::add('two-factor-used:'.$user->id.':'.$code, true, now()->addMinutes(3));
    }

    /** @return list<string> "abcde-fghij" */
    public static function newRecoveryCodes(): array
    {
        return array_map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)), range(1, self::RECOVERY_CODES));
    }

    /**
     * Uses up a recovery code: true if it was one of the user's unused codes.
     */
    public static function useRecoveryCode(User $user, string $code): bool
    {
        $code = Str::lower(trim($code));
        $codes = $user->two_factor_recovery_codes ?? [];
        $index = array_search($code, $codes, true);
        if ($code === '' || $index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }

    public static function reset(User $user): void
    {
        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
    }

    private static function engine(): Google2FA
    {
        return new Google2FA;
    }
}
