<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;

final readonly class QrFrameStyle
{
    public function __construct(
        public FrameShape $shape = FrameShape::None,
        public ?string $label = null,
        public ?string $labelColor = null,
        public ?string $frameColor = null,
        public ?string $header = null,
        public bool $decorative = true,
        public float $decorativeOpacity = 0.6,
    ) {
        if (null !== $header && mb_strlen($header) > 60) {
            throw new \InvalidArgumentException('Frame header must be 60 characters or fewer.');
        }
        if (null !== $labelColor && !preg_match('/^#[0-9a-fA-F]{6}$/', $labelColor)) {
            throw new \InvalidArgumentException(sprintf('Invalid color "%s".', $labelColor));
        }
        if (null !== $frameColor && !preg_match('/^#[0-9a-fA-F]{6}$/', $frameColor)) {
            throw new \InvalidArgumentException(sprintf('Invalid color "%s".', $frameColor));
        }

        if (null !== $label && mb_strlen($label) > 60) {
            throw new \InvalidArgumentException('Frame label must be 60 characters or fewer.');
        }
    }

    /**
     * Builds a frame from the public option names shared by the HTTP API,
     * user presets and the Twig component. Returns null when no frame was
     * requested at all.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ?self
    {
        $shape = FrameShape::from((string) ($data['frameShape'] ?? 'none'));
        if (FrameShape::None === $shape && !isset($data['frameLabel']) && !isset($data['frameHeader'])) {
            return null;
        }

        return new self(
            shape: $shape,
            label: isset($data['frameLabel']) && '' !== $data['frameLabel'] ? (string) $data['frameLabel'] : null,
            labelColor: isset($data['frameLabelColor']) ? (string) $data['frameLabelColor'] : null,
            frameColor: isset($data['frameColor']) ? (string) $data['frameColor'] : null,
            header: isset($data['frameHeader']) && '' !== $data['frameHeader'] ? (string) $data['frameHeader'] : null,
            decorative: self::boolValue($data['frameDecorative'] ?? true),
            decorativeOpacity: isset($data['frameDecorativeOpacity']) ? (float) $data['frameDecorativeOpacity'] : 0.6,
        );
    }

    private static function boolValue(mixed $value): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (null === $filtered) {
            throw new \InvalidArgumentException(sprintf('Invalid boolean value "%s".', (string) $value));
        }

        return $filtered;
    }
}
