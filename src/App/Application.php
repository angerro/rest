<?php

namespace App;

use App\Exchange\Response;
use App\Utility\Config;
use App\Utility\Database;
use Exception;

class Application
{
    /**
     * @throws Exception
     */
    public function init(): void
    {
        // Инициализация конфига
        $config = include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';
        Config::init($config);

        // Инициализация подключения к базе
        Database::initConnection();
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        try {
            $this->init();
            $router = new Router();
            $router->executeAction();
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
