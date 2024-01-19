<?php

namespace App\Exchange;

class Response
{
    private const SUCCESS_STATUS = 'success';
    private const ERROR_STATUS = 'error';

    public static function success($message, int $code = 200): void
    {
        self::setHeaders();

        http_response_code($code);

        echo json_encode([
            'status'  => self::SUCCESS_STATUS,
            'message' => self::getSafeMessage($message),
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function error($message, int $code = 500): void
    {
        self::setHeaders();

        http_response_code($code);


        echo json_encode([
            'status'  => self::ERROR_STATUS,
            'message' => self::getSafeMessage($message),
        ], JSON_UNESCAPED_UNICODE);
    }

    private static function setHeaders(): void
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json; charset=UTF-8");
    }

    private static function getSafeMessage($message)
    {
        if (is_object($message) || is_array($message)) {
            array_walk_recursive($message, function (&$message) {
                $message = html_entity_decode($message);
            });
        } else {
            $message = html_entity_decode($message);
        }

        return $message;
    }
}
