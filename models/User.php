<?php

namespace models;

use classes\core\Model;
use classes\helpers\Logger;

class User extends Model
{
    public int $vk_id;
    public DialogueStages|false $dialogue_stage = false;
    protected string $table = "users";

    public static function getById($vk_id) {
        $instance = new self();
        $query = "SELECT * FROM `" . $instance->table . "` WHERE `vk_id` = :vk_id";
        $statement = $instance->connection->prepare($query);
        $statement->bindValue(":vk_id", $vk_id, \PDO::PARAM_INT);
        if (!$statement->execute()) {
            Logger::log("FATAL", __CLASS__, "User getById query execution error","", "logs/database.log");
            Logger::log("FATAL", __CLASS__, "Query: ",$query, "logs/database.log");
            exit;
        };
        $result = $statement->fetchAll(\PDO::FETCH_CLASS, User::class);
        if (!empty($result)) {
            Logger::log("INFO", __CLASS__, "getById successful", "", "logs/database.log");
            $result[0]->dialogue_stage = DialogueStages::getByUserId($result[0]->vk_id);
            return $result[0];
        }
        else {
            Logger::log("ERROR", __CLASS__, "getById failed", "No result found for vk_id: $vk_id", "logs/database.log");
            return false;
        }
    }

    public static function add($vk_id, $stage = "root"):false|self
    {
        $self = new self();
        $query = "INSERT INTO `$self->table` (`vk_id`) VALUES (:vk_id)";
        $self->connection->beginTransaction();
        $statement = $self->connection->prepare($query);
        $statement->bindValue(":vk_id", $vk_id);
        try {
            $statement->execute();
        }
        catch (\PDOException $e) {
            Logger::log("ERROR", __CLASS__, "User insert failed", $e->getMessage(), "logs/database.log");
            $self->connection->rollBack();
            return false;
        }
        try {
            $dialogueStage = DialogueStages::add($vk_id, $stage);
        }
        catch (\PDOException $e) {
            Logger::log("ERROR", __CLASS__, "Dialogue stage add", $e->getMessage(), "logs/database.log");
            $self->connection->rollBack();
            return false;
        }
        $self->connection->commit();
        $result = self::getById($vk_id);
        $result->dialogue_stage = $dialogueStage;
        return $result;
    }
}
