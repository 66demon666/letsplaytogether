<?php

namespace controllers;

use classes\core\Application;
use classes\core\Controller;

class RoomsController extends Controller
{
    public function create_room($messageObject)
    {
        Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
            "user_id" => 715883939,
            "message" => "Введите название комнаты: ",
            "random_id" => 0,
        ]);
    }



    public function input_room_name($messageObject, $room)
    {
        Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
            "user_id" => 715883939,
            "message" => "Название комнаты: $room",
            "random_id" => 0,
        ]);
    }
    public function input_room_game($messageObject, $game)
    {
        Application::$VKApiClient->messages()->send(Application::ACCESS_TOKEN, [
            "user_id" => 715883939,
            "message" => "Игра: $game",
            "random_id" => 0,
        ]);
    }
}

