<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

final readonly class QrMatrix
{
    /** @param list<list<bool>> $cells */
    public function __construct(public array $cells)
    {
        $size = count($cells);
        if ($size === 0 || $size !== count($cells[0] ?? [])) {
            throw new \InvalidArgumentException('QR matrix must be a non-empty square matrix.');
        }

        foreach ($cells as $row) {
            if (count($row) !== $size) {
                throw new \InvalidArgumentException('QR matrix must be a non-empty square matrix.');
            }
        }
    }

    public function size(): int
    {
        return count($this->cells);
    }

    public function isDark(int $x, int $y): bool
    {
        return $this->cells[$y][$x] ?? false;
    }
}
