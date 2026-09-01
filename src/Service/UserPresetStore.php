<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

final class UserPresetStore
{
    private const FILENAME = 'user_presets.json';

    /** @var array<string, array{config: array<string, mixed>}>|null */
    private ?array $cache = null;

    public function __construct(
        private readonly string $storageDir,
    ) {
    }

    /**
     * @return array<string, array{config: array<string, mixed>}>
     */
    public function all(): array
    {
        return $this->load();
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(string $name): array
    {
        $data = $this->load();
        if (!isset($data[$name])) {
            throw new \InvalidArgumentException(sprintf('Unknown user preset "%s".', $name));
        }

        return $data[$name]['config'];
    }

    /**
     * @param array<string, mixed> $config
     */
    public function save(string $name, array $config): void
    {
        $name = trim($name);
        if ('' === $name) {
            throw new \InvalidArgumentException('Preset name cannot be empty.');
        }
        if (mb_strlen($name) > 60) {
            throw new \InvalidArgumentException('Preset name must be 60 characters or fewer.');
        }
        $data = $this->load();
        $data[$name] = ['config' => $config];
        $this->persist($data);
    }

    public function delete(string $name): void
    {
        $data = $this->load();
        if (!isset($data[$name])) {
            return;
        }
        unset($data[$name]);
        $this->persist($data);
    }

    /**
     * @return array<string, array{config: array<string, mixed>}>
     */
    private function load(): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }
        $path = $this->path();
        if (!is_file($path)) {
            $this->cache = [];

            return $this->cache;
        }
        $raw = file_get_contents($path);
        $data = json_decode($raw ?: '[]', true);
        $this->cache = is_array($data) ? $data : [];

        return $this->cache;
    }

    /**
     * @param array<string, array{config: array<string, mixed>}> $data
     */
    private function persist(array $data): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0o775, true);
        }
        file_put_contents($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        $this->cache = $data;
    }

    private function path(): string
    {
        return rtrim($this->storageDir, '/\\').DIRECTORY_SEPARATOR.self::FILENAME;
    }
}
