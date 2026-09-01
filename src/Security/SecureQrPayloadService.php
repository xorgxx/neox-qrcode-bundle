<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Security;

use Xorgxx\NeoxQrCodeBundle\Model\SecureQrPayload;

final readonly class SecureQrPayloadService
{
    public function __construct(
        private string $secret,
        private SingleUseTokenStoreInterface $tokenStore,
    ) {
        if (strlen($this->secret) < 32) {
            throw new \InvalidArgumentException('NEOX_QRCODE_SECRET must contain at least 32 characters.');
        }
    }

    /** @param array<string,mixed> $data */
    public function sign(array $data, ?\DateTimeImmutable $expiresAt = null, bool $singleUse = false): string
    {
        $payload = [
            'v' => 1,
            'data' => $data,
            'exp' => $expiresAt?->getTimestamp(),
            'jti' => $singleUse ? bin2hex(random_bytes(16)) : null,
            'one' => $singleUse,
        ];

        $encoded = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $encoded, $this->secret, true));

        return $encoded.'.'.$signature;
    }

    public function verify(string $token, bool $consume = false): SecureQrPayload
    {
        [$encoded, $signature] = $this->splitToken($token);
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $encoded, $this->secret, true));

        if (!hash_equals($expected, $signature)) {
            throw new \InvalidArgumentException('Invalid QR signature.');
        }

        /** @var array{v:int,data:array<string,mixed>,exp:?int,jti:?string,one:bool} $payload */
        $payload = json_decode($this->base64UrlDecode($encoded), true, 512, JSON_THROW_ON_ERROR);

        if (($payload['exp'] ?? null) !== null && time() > (int) $payload['exp']) {
            throw new \InvalidArgumentException('QR token has expired.');
        }

        $singleUse = $payload['one'];
        $jti = isset($payload['jti']) ? (string) $payload['jti'] : null;

        if ($singleUse && null !== $jti) {
            if ($consume) {
                if (!$this->tokenStore->consume($jti)) {
                    throw new \InvalidArgumentException('QR token has already been used.');
                }
            } elseif ($this->tokenStore->isConsumed($jti)) {
                throw new \InvalidArgumentException('QR token has already been used.');
            }
        }

        return new SecureQrPayload(
            data: $payload['data'],
            expiresAt: $payload['exp'] ?? null,
            jti: $jti,
            singleUse: $singleUse,
        );
    }

    /** @param array<string,mixed> $data */
    public function encrypt(array $data, ?\DateTimeImmutable $expiresAt = null): string
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            throw new \RuntimeException('The sodium extension is required for encrypted QR payloads.');
        }

        $payload = json_encode([
            'v' => 1,
            'data' => $data,
            'exp' => $expiresAt?->getTimestamp(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        $key = hash('sha256', $this->secret, true);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($payload, $nonce, $key);

        return 'enc.'.$this->base64UrlEncode($nonce.$cipher);
    }

    /** @return array<string,mixed> */
    public function decrypt(string $token): array
    {
        if (!function_exists('sodium_crypto_secretbox_open')) {
            throw new \RuntimeException('The sodium extension is required for encrypted QR payloads.');
        }
        if (!str_starts_with($token, 'enc.')) {
            throw new \InvalidArgumentException('Invalid encrypted QR token.');
        }

        $raw = $this->base64UrlDecode(substr($token, 4));
        $nonceSize = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
        $nonce = substr($raw, 0, $nonceSize);
        $cipher = substr($raw, $nonceSize);
        $key = hash('sha256', $this->secret, true);
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);

        if (false === $plain) {
            throw new \InvalidArgumentException('Unable to decrypt QR token.');
        }

        /** @var array{data:array<string,mixed>,exp:?int} $payload */
        $payload = json_decode($plain, true, 512, JSON_THROW_ON_ERROR);
        if (($payload['exp'] ?? null) !== null && time() > (int) $payload['exp']) {
            throw new \InvalidArgumentException('QR token has expired.');
        }

        return $payload['data'];
    }

    /** @return array{string,string} */
    private function splitToken(string $token): array
    {
        $parts = explode('.', $token, 2);
        if (2 !== count($parts) || '' === $parts[0] || '' === $parts[1]) {
            throw new \InvalidArgumentException('Malformed QR token.');
        }

        return [$parts[0], $parts[1]];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;
        if (0 !== $padding) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if (false === $decoded) {
            throw new \InvalidArgumentException('Invalid base64url payload.');
        }

        return $decoded;
    }
}
