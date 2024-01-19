<?php

namespace App\Entity;

use App\Exchange\Response;
use App\Utility\Database;
use PDO;

abstract class AbstractDbEntity implements BaseEntity
{
    protected int $id;

    protected array $requestData;

    protected string $tableName;

    protected array $tableColumns;

    protected ?PDO $connection;

    public function __construct(?int $id, ?array $requestData)
    {
        if ($id > 0) {
            $this->id = $id;
        }

        if (!empty($requestData)) {
            $this->requestData = $requestData;
        }

        $this->connection = Database::getConnection();
        $this->fillTableColumns();
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function indexAction(): void
    {
        $sth = $this->connection->prepare("SELECT * FROM " . $this->tableName);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            Response::error('Записи не найдены', 404);
            return;
        }

        Response::success($result);
    }

    public function viewAction(): void
    {
        $sth = $this->connection->prepare("SELECT * FROM " . $this->tableName . " WHERE id = " . $this->id);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            Response::error('Запись не найдена', 404);
            return;
        }

        Response::success($result);
    }

    public function createAction(): void
    {
        $firstChunk = implode(', ', $this->tableColumns);
        $t = [];
        foreach ($this->tableColumns as $column) {
            $t[] = ':' . $column;
        }
        $secondChunk = implode(', ', $t);

        $statement = "INSERT INTO " . $this->tableName . " (" . $firstChunk . ") VALUES (" . $secondChunk . ");";
        $statement = $this->connection->prepare($statement);
        $statement->execute($this->requestData);

        if (!$statement->rowCount()) {
            Response::error('Ошибка добавления записи', 501);
            return;
        }

        Response::success('Запись успешно добавлена', 201);
    }

    public function updateAction(): void
    {
        if (!$this->checkExists()) {
            Response::error('Обновляемая запись отсутствует', 501);
            return;
        }

        $t = [];
        foreach ($this->tableColumns as $column) {
            $t[] = $column . ' = :' . $column;
        }
        $chunk = implode(', ', $t);

        $statement = "UPDATE " . $this->tableName . " SET " . $chunk . " WHERE id = :id;";
        $statement = $this->connection->prepare($statement);
        $statement->execute($this->requestData + ['id' => $this->id]);

        Response::success('Запись успешно обновлена');
    }

    public function deleteAction(): void
    {
        if (!$this->checkExists()) {
            Response::error('Удаляемая запись отсутствует', 501);
            return;
        }

        $this->connection->prepare("DELETE FROM " . $this->tableName . " WHERE id = " . $this->id)
            ->execute();

        Response::success('Запись успешно удалена');
    }

    public function checkExists(): bool
    {
        $sth = $this->connection->prepare("SELECT EXISTS(SELECT 1 FROM " . $this->tableName .
            " WHERE id =:id LIMIT 1)");
        $sth->bindValue(':id', $this->id, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_NUM);

        return (bool)$result[0];
    }

    private function fillTableColumns(): void
    {
        $stmt = Database::getConnection()->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS " .
            " WHERE TABLE_SCHEMA= database() AND TABLE_NAME = :table_name;");
        $stmt->execute([
            'table_name' => $this->tableName
        ]);
        foreach ($stmt as $row) {
            if ($row['COLUMN_NAME'] === 'id') {
                continue;
            }
            $this->tableColumns[] = $row['COLUMN_NAME'];
        }
    }
}
