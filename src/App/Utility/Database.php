<?php

namespace App\Utility;

use Exception;
use PDO;

class Database
{
    private static ?PDO $connection = null;

    private function __clone()
    {
    }

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function getConnection(): ?PDO
    {
        if (self::$connection === null) {
            self::initConnection();
        }

        return self::$connection;
    }

    public static function initConnection(): void
    {
        try {
            $host = Config::get('db', 'host');
            $dbName = Config::get('db', 'dbname');
            $user = Config::get('db', 'user');
            $password = Config::get('db', 'password');

            self::$connection = new PDO("mysql:host=" . $host . ";dbname=" . $dbName, $user, $password);
            self::$connection->exec("set names utf8");
        } catch (Exception $exception) {
            throw new Exception("Ошибка подключения: " . $exception->getMessage());
        }
    }
}
