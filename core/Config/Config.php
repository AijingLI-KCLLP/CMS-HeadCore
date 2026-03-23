<?php

namespace Core\Config;

class Config {
    private static array $cache = [];
    private static string $configPath = __DIR__ . '/../../config/';

    public static function get(string $key, mixed $default = null): mixed {
        [$file, $dotPath] = self::parseKey($key);

        if (!isset(self::$cache[$file])) {
            self::$cache[$file] = self::load($file);
        }

        if ($dotPath === null) {
            return self::$cache[$file] ?? $default;
        }

        return self::resolve(self::$cache[$file], $dotPath, $default);
    }

    private static function parseKey(string $key): array {
        $parts = explode('.', $key, 2);
        return [$parts[0], $parts[1] ?? null];
    }

    private static function load(string $file): array {
        $path = self::$configPath . $file . '.json';

        if (!file_exists($path)) {
            throw new \RuntimeException("Fichier de configuration introuvable : $file.json");
        }

        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Erreur de parsing JSON dans $file.json : " . json_last_error_msg());
        }

        return $decoded;
    }

    private static function resolve(array $data, string $path, mixed $default): mixed {
        foreach (explode('.', $path) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }

    public static function clearCache(): void {
        self::$cache = [];
    }
}