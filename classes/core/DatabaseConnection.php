<?php

namespace classes\core;

use classes\helpers\Logger;
use PDO;

/**
 * Class DatabaseConnection
 *
 * Класс для управления соединением с базой данных.
 */
class DatabaseConnection
{
    /**
     * @var null|PDO Статическое свойство для хранения экземпляра PDO.
     */
    public static null|PDO $connection = null;

    /**
     * Получает соединение с базой данных.
     *
     * Если соединение уже установлено, метод возвращает его.
     * В противном случае он пытается установить новое соединение.
     *
     * @return false|PDO Возвращает экземпляр PDO или false, если соединение не удалось установить.
     */
    public static function getConnection(): false|PDO
    {
        if (self::$connection) {
            return self::$connection;
        }
        else {
            try {
                self::$connection = new PDO("mysql:dbname=u2445536_default;host=localhost; charset=utf8", "u2445536_default", "lHg6VpmlY0ENPI91");
                Logger::log("INFO", get_called_class(), "Database connection established", "", "logs/database.log");
                return self::$connection;
            } catch (\PDOException $e) {
                Logger::log("FATAL", get_called_class(), "Database connection error", $e->getMessage(), "logs/database.log");
                return false;
            }
        }
    }
}
