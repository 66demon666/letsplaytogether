<?php

namespace classes\core;

use classes\helpers\Logger;
use classes\routing\Router;
use models\User;
use VK\Client\VKApiClient;

/**
 * Класс Application хранит глобальную конфигурацию приложения, чтобы из любого места кода можно было получить
 * доступ к ключевым объектам
 *
 * @property-read string SECRET Хранит строку из настроек приложения ВК (app_secret)
 * @property-read string ACCESS_TOKEN хранит access_token для выполнения запросов к API VK
 * @property-read integer GROUP_ID Хранит ID группы от лица которой работает бот
 * @property-read string CONFIRMATION_TOKEN Содержит строку, которая должна содержаться в ответе на запрос confirmation
 * @property VKApiClient $VKApiClient Экземпляр класса для выполнения запросов из VK PHP SDK
 * @property RequestParser $RequestParser
 * @property object $data Объект который приходит в ответе от VK API
 */

class Application
{
    public const string SECRET = 'aadfadsfsda13e423q';
    public const string ACCESS_TOKEN = "vk1.a.oa6MGOPFdomDm8V_QkFU57UuOAMpZeHv5Cp4RVi1-AuMLCucN7VOYfl1Mh7qr8H2HUFCOq6SUEs02muh2Zv8pWEFQGO0bInJqEgA1A8RO8zyg31KqohBkmIXcJzBgyVadRzN_jdLuWHx8vZEvZ_bsOrsMHlx0kNQRosFYKn8P6OhaQ877ZiWy0DqKWB1w1mwZOGjF0yvwkB8Etk4HMmCIg";
    public const int GROUP_ID = 224340744;
    public const string CONFIRMATION_TOKEN = '7ca3b637';

    public static VKApiClient $VKApiClient;
    public static RequestParser $RequestParser;

    protected static object $data;

    /**
     * Запускает процесс обработки запроса.
     * Обеспечивает ответ OK в ответ на запрос вне зависимости от наличия ошибок
     *
     * @param object $data Данные полученные от VK API в виде объекта
     * @return void Метод ничего не возвращает, весь вывод происходит в методах контроллера
     */

    public static function run(object $data): void
    {
       // ob_start();
        Logger::init("log.log");
        Logger::log("INFO", get_called_class(), "Application started", "", "logs/core.log");
        self::$VKApiClient = new VKApiClient();
        self::$RequestParser = new RequestParser();
        self::$data = $data;
        Logger::log("INFO", get_called_class(), "Incoming data: " . json_encode(self::$data, JSON_UNESCAPED_UNICODE), "", "logs/core.log");
        try {
            @self::parseRequest();
        } catch (\Exception $e) {
            Logger::log("FATAL", get_called_class(), "Request parsing is failed: ", $e->getMessage(), "logs/core.log");
        } finally {
            Logger::log("INFO", get_called_class(), "Output buffer is clear: ", ob_get_contents(), "logs/core.log");
           // ob_clean();
            header("HTTP/2 200 OK");
            echo "ok";
            Logger::log("INFO", get_called_class(), "OK response send. Application is over.", "", "logs/core.log");
            Logger::log("INFO", get_called_class(), "===========================================================", "", "logs/core.log");
        }
    }


    protected static function parseRequest(): void
    {
        self::$RequestParser->parseObject(
            self::$data->group_id,
            self::SECRET,
            self::$data->type,
            (isset(self::$data->object) ? (array)self::$data->object : (array)self::$data));
    }
}
