<?php

namespace App\Utility;

use Exception;

class Config
{
    private static array|null $data = null;

    private function __clone()
    {
    }

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function init(array $data): void
    {
        if (self::$data !== null) {
            throw new Exception('Конфигурационный файл уже был проинициализирован');
        }
        self::$data = $data;
    }

    public static function get(...$keys): array|string
    {
        $config = self::$data;
        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
            }
        }

        return $config;
    }
}
