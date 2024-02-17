<?php

namespace classes\helpers;

/**
 * Class Logger
 *
 * Класс для логирования событий и ошибок.
 */
class Logger
{
    /**
     * @var string $file Имя файла, в который будет записан лог.
     */
    protected static string $file = "log.log";

    /**
     * Инициализирует логгер и устанавливает имя файла для записи лога.
     *
     * @param string $file Имя файла для записи лога.
     * @return void
     */
    public static function init($file="log.log"):void
    {
        self::$file = $file;
    }

    /**
     * Записывает сообщение в лог.
     *
     * @param string $level Уровень логирования (INFO, WARNING, ERROR, etc.).
     * @param string $class Имя класса, где произошло событие.
     * @param string $event Описание события.
     * @param string $errorText Описание ошибки, если она есть.
     * @param string $file Имя файла для записи лога. Если не указано, используется значение из self::$file.
     * @return void
     */
    public static function log($level, $class, $event, $errorText = "", $file=""): void
    {
        if (empty($file)) {
            $fileHandler = fopen(self::$file, "a");
        }
        else {
            $fileHandler = fopen($file, "a");
        }
        $logMessage = "[" . date('d/m/Y-H:i:s', time()) . "][$level][$class] Event: $event";
        if (!empty($errorText)) {
            $logMessage .= ", Error: $errorText";
        }
        fwrite($fileHandler, $logMessage . "\n");
        fclose($fileHandler);
    }
}
