<?php

namespace App\Services;

/**
 * RFC 6238 TOTP implementation — no third-party package required.
 * Uses HMAC-SHA1 with a 30-second window and 6-digit codes.
 */
class TotpService
{
    private const CHARS   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const DIGITS  = 6;
    private const WINDOW  = 30;
    private const DRIFT   = 1; // accept one window before/after to handle clock skew

    public function generateSecret(): string
    {
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= self::CHARS[random_int(0, 31)];
        }
        return $secret;
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (strlen($code) !== self::DIGITS || ! ctype_digit($code)) {
            return false;
        }

        $counter = (int) floor(time() / self::WINDOW);
        for ($drift = -self::DRIFT; $drift <= self::DRIFT; $drift++) {
            if ($this->hotp($secret, $counter + $drift) === $code) {
                return true;
            }
        }
        return false;
    }

    public function qrCodeUrl(string $secret, string $email, string $issuer = 'Fenroy Admin'): string
    {
        $label = rawurlencode("{$issuer}:{$email}");
        return "otpauth://totp/{$label}?secret={$secret}&issuer=" . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30';
    }

    private function hotp(string $secret, int $counter): string
    {
        $key     = $this->base32Decode($secret);
        $message = pack('J', $counter); // big-endian 64-bit
        $hash    = hash_hmac('sha1', $message, $key, true);
        $offset  = ord($hash[19]) & 0x0F;
        $otp     = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
            ((ord($hash[$offset + 3]) & 0xFF))
        ) % (10 ** self::DIGITS);
        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $input): string
    {
        $input  = strtoupper(trim($input));
        $output = '';
        $buffer = 0;
        $bits   = 0;
        foreach (str_split($input) as $char) {
            $val = strpos(self::CHARS, $char);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bits  += 5;
            if ($bits >= 8) {
                $bits  -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }
        return $output;
    }
}
