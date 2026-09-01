<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Security;

interface SingleUseTokenStoreInterface
{
    /**
     * Atomically marks a token identifier as consumed.
     *
     * Returns true if the token was successfully consumed (first call),
     * false if it was already consumed.
     */
    public function consume(string $jti): bool;

    /**
     * Checks whether a token identifier has been consumed.
     */
    public function isConsumed(string $jti): bool;
}
