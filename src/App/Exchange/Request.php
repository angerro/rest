<?php

namespace App\Exchange;

use App\Entity\AbstractDbEntity;
use App\Utility\Config;
use App\Utility\Database;
use Exception;

class Request
{
    private array $requestChunks;
    private string $requestMethod;
    private string $requestAction;
    private string $requestEntity;
    private ?int $requestElementId;
    private ?array $requestData;
    public function __construct()
    {
        $this->fillRequestChunks();
        $this->fillRequestMethod();
        $this->fillRequestAction();
        $this->fillRequestEntity();
        $this->fillRequestData();
        $this->checkRequestData();
        $this->fillRequestElementId();
    }

    private function fillRequestChunks(): void
    {
        $this->requestChunks = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    }

    private function fillRequestMethod(): void
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        if ($this->requestMethod == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->requestMethod = 'DELETE';
            } elseif ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->requestMethod = 'PUT';
            } else {
                throw new Exception("Неизвестный метод запроса");
            }
        }
    }

    private function fillRequestAction(): void
    {
        switch ($this->requestMethod) {
            case 'GET':
                if (!empty($this->requestChunks[2])) {
                    $this->requestAction = 'viewAction';
                } else {
                    $this->requestAction =  'indexAction';
                }
                break;
            case 'POST':
                $this->requestAction =  'createAction';
                break;
            case 'PUT':
                $this->requestAction =  'updateAction';
                break;
            case 'DELETE':
                $this->requestAction =  'deleteAction';
                break;
            default:
                throw new Exception("Не удалось определить действие");
        }
    }

    private function fillRequestEntity(): void
    {
        if (empty($this->requestChunks[1])) {
            throw new Exception("Не указана сущность запроса");
        }

        $requestEntity = Config::get('route_list', $this->requestChunks[1]);

        if (!$requestEntity || !is_string($requestEntity)) {
            throw new Exception("Невозможно выполнить запрос к сущности '{$this->requestChunks[1]}'");
        }

        $this->requestEntity = $requestEntity;
    }

    private function fillRequestElementId(): void
    {
        $elementId = $this->requestChunks[2];

        if (
            (!empty($elementId) && !is_numeric($elementId)) ||
            (!empty($elementId) && intval($elementId) <= 0)
        ) {
            throw new Exception("Некорректное значение идентификатора элемента");
        }
        if (!empty($elementId)) {
            $elementId = intval($elementId);
        }
        if (empty($elementId)) {
            $elementId = null;
        }

        $this->requestElementId = $elementId;
    }

    private function fillRequestData(): void
    {
        $phpInput = file_get_contents("php://input");

        if (!empty($phpInput)) {
            $this->requestData = json_decode($phpInput, true);
        } else {
            $this->requestData = null;
        }
    }

    private function checkRequestData(): void
    {
        if (!in_array($this->requestAction, ['createAction', 'updateAction'])) {
            return;
        }

        if (is_null($this->requestData)) {
            throw new Exception("Переданы невалидные данные");
        }

        /**
         * @var AbstractDbEntity $entity
         */
        $entity = new $this->requestEntity(null, null);
        $tableName = $entity->getTableName();

        $tableColumns = [];
        $stmt = Database::getConnection()->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS " .
            " WHERE TABLE_SCHEMA= database() AND TABLE_NAME = :table_name;");
        $stmt->execute([
            'table_name' => $tableName
        ]);
        foreach ($stmt as $row) {
            if ($row['COLUMN_NAME'] === 'id') {
                continue;
            }
            $tableColumns[] = $row;
        }

        foreach ($tableColumns as $column) {
            if (!in_array($column['COLUMN_NAME'], array_keys($this->requestData))) {
                throw new Exception("В переданных данных отсутствует '{$column['COLUMN_NAME']}'");
            }

            $requestColumnLength = mb_strlen(strval($this->requestData[$column['COLUMN_NAME']]));
            $columnMaxLength = intval($column['CHARACTER_MAXIMUM_LENGTH']);
            if ($requestColumnLength > $columnMaxLength) {
                throw new Exception("Длина данных в поле '{$column['COLUMN_NAME']}' превышет допустимую: " .
                    "'{$columnMaxLength}'. Передано '" . $requestColumnLength . "'");
            }
        }

        foreach (array_keys($this->requestData) as $key) {
            if (!in_array($key, array_column($tableColumns, 'COLUMN_NAME'))) {
                throw new Exception("В переданных данных присутствует лишнее поле '$key'");
            }
        }
    }

    public function getRequestAction(): string
    {
        return $this->requestAction;
    }

    public function getRequestEntity(): string
    {
        return $this->requestEntity;
    }

    public function getRequestElementId(): ?string
    {
        return $this->requestElementId;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }
}
