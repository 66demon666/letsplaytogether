<?php

namespace classes\routing;

use classes\helpers\Logger;
use models\DialogueStages;
use models\User;

/**
 * Class DialogDispatcher
 *
 * Класс для управления диалогами.
 */
class DialogDispatcher
{
    /**
     * @var array $dialogue Содержит структуру диалога. Каждый элемент массива представляет собой этап диалога, который содержит:
     * - "stage" (string): Название этапа.
     * - "pattern" (string): Регулярное выражение для сопоставления ввода пользователя.
     * - "action" (array): Действие, которое нужно выполнить на этом этапе. Содержит:
     *   - "controller" (string): Класс контроллера, который должен обработать действие.
     *   - "method" (string): Метод контроллера, который должен быть вызван.
     * - "forks" (array|null): Массив возможных переходов на другие этапы. Каждый переход содержит те же поля, что и основной этап.
     */
    public array $dialogue = [
        [
            "stage" => "root",
            "forks" => [
                [
                    "stage" => "start",
                    "pattern" => "~^Начать$~",
                    "action" => [
                        "controller" => \controllers\DialogController::class,
                        "method" => "start"
                    ],
                    "forks" => [
                        [
                            "stage" => "search_rooms",
                            "pattern" => "~^Искать комнаты$~",
                            "action" => [
                                "controller" => \controllers\RoomsController::class,
                                "method" => "search_rooms"
                            ]
                        ],
                        [
                            "stage" => "create_room",
                            "pattern" => "~^Создать комнату$~",
                            "action" => [
                                "controller" => \controllers\RoomsController::class,
                                "method" => "create_room"
                            ],
                            "forks"=> [
                                [
                                    "stage" => "input_room_name",
                                    "pattern" => "~^(.*)$~",
                                    "action" => [
                                        "controller" => \controllers\RoomsController::class,
                                        "method" => "input_room_name"
                                    ],
                                    "forks"=> [
                                        [
                                            "stage" => "input_room_game",
                                            "pattern" => "~^(.*)$~",
                                            "action" => [
                                                "controller" => \controllers\RoomsController::class,
                                                "method" => "input_room_game"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * Находит узел диалога по названию стадиии.
     *
     * @param array $dialogue Массив, содержащий структуру диалога.
     * @param string $stage Стадия диалога.
     * @return false|array Возвращает узел диалога или false, если узел не найден.
     */
    /**
     * Ищет узел в диалоге по заданному этапу.
     *
     * @param array $dialogue Массив узлов диалога для поиска.
     * @param string $stage Этап, который нужно найти.
     * @return false|array Возвращает узел с заданным этапом, если он найден, иначе возвращает false.
     */
    private function findNodeByStage(array $dialogue, string $stage): false|array
    {
        // Проходим по каждому узлу в диалоге
        foreach ($dialogue as $node) {
            // Если этап узла совпадает с искомым этапом, возвращаем этот узел
            if ($node["stage"] === $stage) {
                return $node;
            }
            // Если у узла есть ветвления (forks)
            if (isset($node["forks"])) {
                // Проходим по каждому ветвлению
                foreach ($node["forks"] as $fork) {
                    // Рекурсивно ищем искомый этап в ветвлениях
                    $result = $this->findNodeByStage($node["forks"], $stage);
                    // Если искомый этап найден в ветвлениях, возвращаем его
                    if ($result !== false) {
                        return $result;
                    }
                }
            }
        }
        // Если искомый этап не найден ни в одном из узлов, возвращаем false
        return false;
    }


    /**
     * Ищет ветвление в диалоге, соответствующее заданному тексту.
     *
     * @param array $forks Массив ветвлений для поиска.
     * @param string $text Текст, который нужно найти.
     * @return false|array Возвращает ветвление и параметры, соответствующие заданному тексту, если они найдены, иначе возвращает false.
     */
    private function findForkByMessage(array $forks, string $text): false|array
    {
        // Инициализируем массив параметров
        $params = array();
        // Проходим по каждому ветвлению
        foreach ($forks as $fork) {
            // Если текст пользователя соответствует шаблону ветвления
            if (preg_match_all($fork["pattern"], $text, $matches, PREG_PATTERN_ORDER) !== 0) {
                // Удаляем полное совпадение из массива совпадений
                unset($matches[0]);
                // Объединяем все совпадения в один массив параметров
                foreach ($matches as $match) {
                    $params = array_merge($params, $match);
                }
                // Записываем параметры в лог
                Logger::log("DEBUG", __CLASS__, "PARAMS: " . json_encode($params, JSON_UNESCAPED_UNICODE), "", "logs/dispatching.log");
                // Возвращаем ветвление и параметры
                return [
                    "fork" => $fork,
                    "params" => $params];
            }
        }
        // Если соответствующее ветвление не найдено, возвращаем false
        return false;
    }


    /**
     * Обрабатывает входящее сообщение и выполняет соответствующее действие.
     *
     * @param array $messageObject Объект сообщения, который нужно обработать.
     */
    public function dispatch(array $messageObject): void
    {
        // Записываем в лог начало обработки сообщения
        Logger::log("INFO", __CLASS__, "Start message dispatch: " . $messageObject["message"]->text, "", "logs/dispatching.log");

        // Получаем пользователя по его ID
        $user = User::getById($messageObject["message"]->from_id);

        // Если пользователь не найден, добавляем его
        if (!$user) {
            $user = User::add($messageObject["message"]->from_id);
            // Если добавление пользователя не удалось, записываем ошибку в лог и прекращаем выполнение
            if (!$user) {
                Logger::log("FATAL", __CLASS__, "User add ", "User " . $messageObject["message"]->from_id . " add error", "logs/dispatching.log");
                return;
            }
            // Записываем в лог информацию о том, что пользователь был добавлен
            Logger::log("INFO", __CLASS__, "User " . $messageObject["message"]->from_id . " was add to database", "", "logs/dispatching.log");
        }

        // Записываем в лог ID пользователя
        Logger::log("DEBUG", __CLASS__, "User VK ID: " . $messageObject["message"]->from_id, "", "logs/dispatching.log");

        // Получаем текущий этап диалога пользователя
        $userStage = $user->dialogue_stage;

        // Если этап диалога пользователя не найден, записываем ошибку в лог и прекращаем выполнение
        if (!$userStage) {
            $dialogueStage = DialogueStages::add($user->vk_id, "start");
            $user->dialogue_stage = $dialogueStage;
            Logger::log("ERROR", get_called_class(), "User dialogue stage not found in database. Created start stage", "", "logs/dispatching.log");
            $userStage = $user->dialogue_stage;
        }

        // Записываем в лог текущий этап диалога пользователя
        Logger::log("DEBUG", __CLASS__, "User dialogue stage: " . $userStage->stage, "", "logs/dispatching.log");

        // Ищем узел диалога, соответствующий текущему этапу пользователя
        $dialogue = $this->findNodeByStage($this->dialogue, $userStage->stage);

        // Если узел диалога не найден, сбрасываем диалог на первый шаг
        if (!$dialogue) {
            $user->dialogue_stage->updateStage("start");
            Logger::log("ERROR", __CLASS__, "Stage " . $userStage->stage . " not found. Reset to start", "", "logs/dispatching.log");
            $dialogue = $this->findNodeByStage($this->dialogue, $userStage->stage);
        }

        // Записываем в лог объект диалога пользователя
        Logger::log("DEBUG", __CLASS__, "User dialogue object: " . json_encode($dialogue, JSON_UNESCAPED_UNICODE), "", "logs/dispatching.log");

        // Ищем ветвление, соответствующее тексту сообщения
        $fork = $this->findForkByMessage($dialogue["forks"], $messageObject["message"]->text);

        // Получаем параметры ветвления
        $params = $fork["params"];
        array_unshift($params, $messageObject);

        // Если ветвление не найдено, записываем ошибку в лог и прекращаем выполнение
        if (!$fork) {
            Logger::log("FATAL", __CLASS__, "Fork for message not found: " . $messageObject["message"]->text, "", "logs/dispatching.log");
            //TODO Ответить пользователю о том, что такой команды нет на данном этапе диалога
            return;
        }

        // Пытаемся вызвать метод контроллера, соответствующий ветвлению
        try {
            Logger::log("DEBUG", __CLASS__, sprintf(
                "Call controller method. Controller %s; Method: %s; Params: %s",
                $fork["fork"]["action"]["controller"],
                $fork["fork"]["action"]["method"],
                json_encode($params, JSON_UNESCAPED_UNICODE)),
                "",
                "logs/dispatching.log");
            $user->dialogue_stage->updateStage($fork["fork"]["stage"]);
            call_user_func([new $fork["fork"]["action"]["controller"], $fork["fork"]["action"]["method"]], ...$params);
        } catch (\Exception $e) {
            // Если вызов метода контроллера не удался, записываем ошибку в лог
            Logger::log("FATAL", __CLASS__, sprintf(
                "Call controller method is failed. Controller %s; Method: %s; Params: %s",
                $fork["fork"]["action"]["controller"],
                $fork["fork"]["action"]["method"],
                json_encode($params, JSON_UNESCAPED_UNICODE)),
                "",
                "logs/dispatching.log");
        }
    }
}
