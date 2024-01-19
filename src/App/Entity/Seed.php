<?php

namespace App\Entity;

use App\Exception;
use App\Exchange\Response;
use App\Utility\Database;

class Seed extends AbstractDbEntity
{
    protected string $tableName = '';

    public function indexAction(): void
    {
        try {
            $query = " DROP TABLE IF EXISTS `records`;

            CREATE TABLE IF NOT EXISTS `records` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `title` varchar(32) NOT NULL,
              `description` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            INSERT INTO `records` (`id`, `title`, `description`) VALUES
            (1, 'LG P880 4X HD', 'My first awesome phone!'),
            (2, 'Google Nexus 4', 'The most awesome phone of 2013!'),
            (3, 'Samsung Galaxy S4', 'How about no?');";

            if (Database::getConnection()->query($query)) {
                Response::success('Таблица с демо-данными готова. Теперь вы можете обращаться к /api/records/ с ' .
                    'помощью Postman.');
            }
        } catch (Exception $e) {
            echo "Ошибка создания демо-данных:" . $e->getMessage();
        }
    }
}
