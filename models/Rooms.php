<?php

namespace models;

use classes\core\Application;
use classes\core\Model;

class Rooms extends Model
{
    public int $id = 0;
    public int $owner = 0;
    public string $name = "";
    public string $game = "";
    public string $description = "";
    public string $status = "";
    public null|\DateTime $created_at = null;

    public static function add(int $owner, $status = "created")
    {
        $self = new self;
        $query = "INSERT INTO rooms (owner_id, status) VALUES (:owner, :status)";
        $statement = $self->connection->prepare($query);
        $statement->bindValue(":owner", $owner);
        $statement->bindValue(":status", $status);
        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
            return
        }
    }

    public static function getById($id)
    {
        $self = new self;
        $query = "SELECT * FROM rooms WHERE id = :id";
        $statement = $self->connection->prepare($query);
        $statement->bindValue(":id", $id);
        if ($statement->execute()) {
    }
}