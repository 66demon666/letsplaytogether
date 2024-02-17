<?php

namespace classes\core;

use classes\helpers\Logger;
use classes\routing\DialogDispatcher;
use controllers\IntegrationController;
use VK\CallbackApi\VKCallbackApiHandler;

require_once "vendor/autoload.php";

/**
 * Class RequestParser
 *
 * Класс для обработки входящих запросов от VK API.
 */
class RequestParser extends VKCallbackApiHandler
{
    /**
     * Обрабатывает запрос на подтверждение сервера от VK API.
     *
     * @param int $group_id ID группы, которую нужно подтвердить.
     * @param string $secret Секретный ключ для подтверждения.
     * @param object $object Объект, содержащий данные запроса.
     * @return void
     */
    function confirmation($group_id, $secret, $object): void
    {
        Logger::log("INFO", __CLASS__, "Confirmation request received", "", "logs/dispatching.log");
        try {
            $controller = new IntegrationController();
            call_user_func(array($controller, "confirmation"), $object);
        } catch (\Exception $e) {
            Logger::log("FATAL", __CLASS__, "Confirmation fatal error: ", $e->getMessage(), "logs/dispatching.log");
        } finally {
            exit;
        }

    }

    /**
     * Обрабатывает событие нового сообщения. Передает все входящие параметры в DialogDispatcher
     *      *
     * @param int $group_id ID группы, которую нужно подтвердить.
     * @param string $secret Секретный ключ для подтверждения.
     * @param object $object Объект, содержащий данные запроса.
     * @return void
     */
    function messageNew($group_id, $secret, $object): void
    {
        try {
            $controller = new DialogDispatcher();
            call_user_func(array($controller, "dispatch"), $object);
        } catch (\Exception $e) {
            Logger::log("FATAL", __CLASS__, "Start dispatching error", $e->getMessage(), "logs/dispatching.log");
        }

    }
}
