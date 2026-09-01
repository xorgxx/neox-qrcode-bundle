<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Xorgxx\NeoxQrCodeBundle\Security\CacheSingleUseTokenStore;
use Xorgxx\NeoxQrCodeBundle\Security\SecureQrPayloadService;
use Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface;

final class SecureQrPayloadServiceTest extends TestCase
{
    private const SECRET = 'test-secret-key-with-at-least-32-characters!!';

    public function testSignAndVerifyRoundTrip(): void
    {
        $service = $this->createService();

        $token = $service->sign(['ticket' => 123]);

        $payload = $service->verify($token);

        self::assertSame(['ticket' => 123], $payload->data);
        self::assertFalse($payload->singleUse);
        self::assertNull($payload->expiresAt);
    }

    public function testSignWithExpiration(): void
    {
        $service = $this->createService();

        $token = $service->sign(
            ['ticket' => 456],
            new \DateTimeImmutable('+15 minutes'),
        );

        $payload = $service->verify($token);

        self::assertSame(['ticket' => 456], $payload->data);
        self::assertNotNull($payload->expiresAt);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $service = $this->createService();

        $token = $service->sign(
            ['ticket' => 789],
            new \DateTimeImmutable('-1 minute'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('expired');
        $service->verify($token);
    }

    public function testInvalidSignatureIsRejected(): void
    {
        $service = $this->createService();

        $token = $service->sign(['data' => 'test']);
        $parts = explode('.', $token);
        $tampered = $parts[0].'.invalidSignature';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid QR signature');
        $service->verify($tampered);
    }

    public function testMalformedTokenIsRejected(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed');
        $service->verify('no-dot-here');
    }

    public function testEmptyTokenPartsAreRejected(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed');
        $service->verify('.');
    }

    public function testSingleUseTokenCanBeConsumedOnce(): void
    {
        $store = new InMemoryTokenStore();
        $service = $this->createService($store);

        $token = $service->sign(
            ['ticket' => 999],
            new \DateTimeImmutable('+15 minutes'),
            singleUse: true,
        );

        $payload = $service->verify($token, consume: true);
        self::assertSame(['ticket' => 999], $payload->data);
        self::assertTrue($payload->singleUse);
        self::assertNotNull($payload->jti);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already been used');
        $service->verify($token, consume: true);
    }

    public function testSingleUseTokenCanBeVerifiedWithoutConsuming(): void
    {
        $store = new InMemoryTokenStore();
        $service = $this->createService($store);

        $token = $service->sign(
            ['ticket' => 999],
            new \DateTimeImmutable('+15 minutes'),
            singleUse: true,
        );

        $payload = $service->verify($token, consume: false);
        self::assertSame(['ticket' => 999], $payload->data);

        $payload = $service->verify($token, consume: false);
        self::assertSame(['ticket' => 999], $payload->data);
    }

    public function testSecretTooShortThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 32');
        $this->createService(null, 'short');
    }

    public function testSignDeterministicForSameInput(): void
    {
        $service = $this->createService();

        $a = $service->sign(['data' => 'test']);
        $b = $service->sign(['data' => 'test']);

        self::assertSame($a, $b);
    }

    public function testSignDifferentDataProducesDifferentTokens(): void
    {
        $service = $this->createService();

        $a = $service->sign(['data' => 'test1']);
        $b = $service->sign(['data' => 'test2']);

        self::assertNotSame($a, $b);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('ext-sodium not available.');
        }

        $service = $this->createService();

        $token = $service->encrypt(['private' => 'value']);

        $data = $service->decrypt($token);

        self::assertSame(['private' => 'value'], $data);
    }

    public function testDecryptInvalidTokenThrows(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('ext-sodium not available.');
        }

        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $service->decrypt('not-encrypted');
    }

    public function testDecryptWithWrongSecretFails(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('ext-sodium not available.');
        }

        $serviceA = $this->createService(null, self::SECRET);
        $serviceB = $this->createService(null, 'another-secret-with-32-characters!!');

        $token = $serviceA->encrypt(['secret' => 'data']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to decrypt');
        $serviceB->decrypt($token);
    }

    public function testEncryptWithExpiration(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('ext-sodium not available.');
        }

        $service = $this->createService();

        $token = $service->encrypt(
            ['data' => 'test'],
            new \DateTimeImmutable('+15 minutes'),
        );

        $data = $service->decrypt($token);

        self::assertSame(['data' => 'test'], $data);
    }

    public function testEncryptExpiredTokenIsRejected(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('ext-sodium not available.');
        }

        $service = $this->createService();

        $token = $service->encrypt(
            ['data' => 'test'],
            new \DateTimeImmutable('-1 minute'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('expired');
        $service->decrypt($token);
    }

    public function testEncryptedTokenHasPrefix(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('ext-sodium not available.');
        }

        $service = $this->createService();

        $token = $service->encrypt(['data' => 'test']);

        self::assertStringStartsWith('enc.', $token);
    }

    public function testCacheSingleUseTokenStoreConsumesAtomically(): void
    {
        $cache = new InMemoryCache();
        $store = new CacheSingleUseTokenStore($cache);

        self::assertTrue($store->consume('jti-1'));
        self::assertFalse($store->consume('jti-1'));
        self::assertTrue($store->isConsumed('jti-1'));
        self::assertFalse($store->isConsumed('jti-2'));
    }

    private function createService(?SingleUseTokenStoreInterface $store = null, string $secret = self::SECRET): SecureQrPayloadService
    {
        return new SecureQrPayloadService($secret, $store ?? new InMemoryTokenStore());
    }
}

final class InMemoryTokenStore implements SingleUseTokenStoreInterface
{
    /** @var array<string, bool> */
    private array $consumed = [];

    public function consume(string $jti): bool
    {
        if (isset($this->consumed[$jti])) {
            return false;
        }

        $this->consumed[$jti] = true;

        return true;
    }

    public function isConsumed(string $jti): bool
    {
        return isset($this->consumed[$jti]);
    }
}

final class InMemoryCache implements CacheInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    /** @param array<string, mixed>|null $metadata */
    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        $save = true;
        $value = $callback($this->createItem($key), $save);
        $this->data[$key] = $value;

        return $value;
    }

    public function delete(string $key): bool
    {
        unset($this->data[$key]);

        return true;
    }

    private function createItem(string $key): ItemInterface
    {
        return new InMemoryCacheItem($key);
    }
}

final class InMemoryCacheItem implements ItemInterface
{
    public function __construct(private string $key)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return null;
    }

    public function isHit(): bool
    {
        return false;
    }

    public function set(mixed $value): static
    {
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        return $this;
    }

    public function tag(string|iterable $tags): static
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function getMetadata(): array
    {
        return [];
    }
}
