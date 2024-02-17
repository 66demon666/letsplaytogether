<?php

namespace controllers;

use classes\core\Application;
use classes\core\Controller;
use classes\helpers\Logger;
use models\DialogueStages;
use models\User;

class DialogController extends Controller
{

    public function search_rooms($messageObject)
    {
        Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
            "user_id" => 715883939,
            "message" => "Сейчас будем искать комнаты",
            "random_id" => 0,
        ]);
    }

    public function greeting($object, $name): void
    {
        $keyboard = [
            "one_time" => true,
            "buttons" => [
                [
                    [
                        "action" => [
                            "type" => "text",
                            "label" => "Искать комнаты",
                        ],
                    ],
                ],
                [
                    [
                        "action" => [
                            "type" => "text",
                            "label" => "Создать комнату",
                        ],
                    ]
                ],
            ]
        ];
        $user = new User();
        $user = $user->getById($object["message"]->from_id);
        if ($user) {
            Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
                "user_id" => $object["message"]->from_id,
                "message" => "Привет, $name! Уже виделись",
                "random_id" => 0,
                "keyboard" => json_encode($keyboard),
            ]);
        } else {
            Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
                "user_id" => $object["message"]->from_id,
                "message" => "Привет, $name! Мы еще не знакомы",
                "random_id" => 0,
                "keyboard" => json_encode($keyboard),
            ]);
            $user = new User();
            $user->vk_id = $object["message"]->from_id;
            $user->name = $name;
            $user->save();
        }

    }

    public function start($object): void
    {
        $keyboard = [
            "one_time" => true,
            "buttons" => [
                [
                    [
                        "action" => [
                            "type" => "callback",
                            "label" => "Искать комнаты",
                        ],
                    ],
                ],
                [
                    [
                        "action" => [
                            "type" => "text",
                            "label" => "Создать комнату",
                        ],
                    ]
                ],
            ]
        ];
        $response = Application::$VKApiClient->users()->get(Application::ACCESS_TOKEN, [
            "user_ids" => $object["message"]->from_id,
        ]);
        Logger::log("debug", json_encode($response, JSON_UNESCAPED_UNICODE), "test.txt");
        $name = $response[0]["first_name"];
        Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
            "user_id" => $object["message"]->from_id,
            "message" => "Привет, $name! Я помогу тебе найти компанию для игры в какую-нибудь игру!\n\nТы можешь либо присоединиться к другой компании, либо создать свою.\n\n Выбирай!",
            "random_id" => 0,
            "keyboard" => json_encode($keyboard),
        ]);
    }
}