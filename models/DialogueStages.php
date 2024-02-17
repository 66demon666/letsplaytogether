<?php

namespace models;

use classes\core\Model;
use classes\helpers\Logger;

class DialogueStages extends Model
{

    public int $vk_id;
    public string $stage;

    public static function getByUserId(int $user_id): false|self
    {
        $self = new self();
        $query = "SELECT dialogue_stages.vk_id, dialogue_stages.stage FROM dialogue_stages INNER JOIN users ON dialogue_stages.vk_id = users.vk_id WHERE users.vk_id = :vk_id";
        $statement = $self->connection->prepare($query);
        $statement->bindValue(":vk_id", $user_id);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
        if (!empty($result)) {
            return $result[0];
        }
        else {
            return false;
        }
    }

    public static function getByName(string $stage):false|self {
        $self = new self();
        $query = "SELECT * FROM `dialogue_stages` WHERE `stage` = :stage";
        $statement = $self->connection->prepare($query);
        $statement->bindValue(":stage", $stage);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
        if (!empty($result)) {
            return $result[0];
        }
        else {
            return false;
        }
    }

    public static function add($vk_id, $stage)
    {
        $self = new self;
        $query = "INSERT INTO dialogue_stages (`vk_id`, `stage`) VALUES (:vk_id, :stage)";
        $statement = $self->connection->prepare($query);
        $statement->bindValue(":vk_id", $vk_id);
        $statement->bindValue(":stage", $stage, \PDO::PARAM_STR);
            if (!$statement->execute()) {
                return false;
            };
            Logger::log("INFO", __CLASS__, "Dialogue stage added", "", "logs/database.log");
            return self::getByUserId($vk_id);
    }

    public function updateStage($newValue) {
        $query = "UPDATE dialogue_stages SET stage = :stage WHERE vk_id = :vk_id";
        $statement = $this->connection->prepare($query);
        $statement->bindValue(":stage", $newValue);
        $statement->bindValue(":vk_id", $this->vk_id);
        if (!$statement->execute()) {
            return false;
        }
        Logger::log("INFO", __CLASS__, "Dialogue stage updated", "", "logs/database.log");
        $this->stage = $newValue;
        return true;
    }

}