<?php

spl_autoload_register(
    function ($class) {
        if (str_starts_with($class, "App")) {
            $pathParts = explode('\\', $class);
            $classPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
                implode(DIRECTORY_SEPARATOR, $pathParts) . '.php';
            if (file_exists($classPath)) {
                require $classPath;
            } else {
                throw new Exception("Ошибка загрузки '$classPath'");
            }
        }
    }
);
