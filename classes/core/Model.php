<?php

namespace classes\core;

use PDO;
use classes\core\DatabaseConnection;

/**
 * Class Model
 *
 * Абстрактный класс модели, который содержит общие свойства и методы для всех моделей.
 */
abstract class Model
{
    /**
     * @var string $table Имя таблицы в базе данных, связанной с этой моделью.
     */
    protected string $table = "";

    /**
     * @var \PDO $connection Экземпляр PDO для взаимодействия с базой данных.
     */
    protected \PDO $connection;

    /**
     * Конструктор класса Model.
     *
     * Устанавливает соединение с базой данных при создании экземпляра модели.
     */
    public function __construct()
    {
        $this->connection = DatabaseConnection::getConnection();
    }
}
