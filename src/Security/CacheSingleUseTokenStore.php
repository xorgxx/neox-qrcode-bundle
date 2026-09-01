<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Security;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CacheSingleUseTokenStore implements SingleUseTokenStoreInterface
{
    private const PREFIX = 'xorgxx_neox_qrcode_consumed_';

    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    public function consume(string $jti): bool
    {
        $key = self::PREFIX . hash('sha256', $jti);
        $alreadyConsumed = $this->cache->get($key, static function (ItemInterface $item): bool {
            $item->expiresAfter(31536000);
            return false;
        });

        if ($alreadyConsumed === true) {
            return false;
        }

        $this->cache->delete($key);
        $this->cache->get($key, static function (ItemInterface $item): bool {
            $item->expiresAfter(31536000);
            return true;
        });

        return true;
    }

    public function isConsumed(string $jti): bool
    {
        $key = self::PREFIX . hash('sha256', $jti);

        return $this->cache->get($key, static function (ItemInterface $item): bool {
            $item->expiresAfter(31536000);
            return false;
        }) === true;
    }
}
