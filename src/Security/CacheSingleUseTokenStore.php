<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Security;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CacheSingleUseTokenStore implements SingleUseTokenStoreInterface
{
    private const PREFIX = 'xorgxx_neox_qrcode_consumed_';
    private const TTL = 31536000;

    public function __construct(
        private readonly AdapterInterface $cache,
    ) {
    }

    public function consume(string $jti): bool
    {
        // CacheInterface::get() guarantees the callback runs at most once per
        // key (stampede protection). Storing the winner's random claim makes
        // the check-and-set atomic for every concurrent caller: whoever reads
        // back a claim different from its own has lost the race. Even on a
        // naive pool without locking, the last write wins so at most one
        // caller can see its own claim persisted.
        $claim = bin2hex(random_bytes(16));
        $winner = $this->cache->get($this->key($jti), static function (ItemInterface $item) use ($claim): string {
            $item->expiresAfter(self::TTL);

            return $claim;
        });

        return $winner === $claim;
    }

    public function isConsumed(string $jti): bool
    {
        // hasItem() is used instead of get(): a simple probe must not create a
        // cache entry, otherwise the token would be marked before the caller
        // ever tries to consume it.
        return $this->cache->hasItem($this->key($jti));
    }

    private function key(string $jti): string
    {
        return self::PREFIX.hash('sha256', $jti);
    }
}
