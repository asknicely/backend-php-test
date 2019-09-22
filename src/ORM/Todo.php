<?php

namespace ORM;

use PDO;

class Todo
{
    private $db;
    private $id;
    private $user_id;
    private $description;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getById(int $id)
    {
        if (!$id) {
            return null;
        }

        $sql = "SELECT * FROM todos WHERE id = :id";

        return $this->db->fetchAssoc($sql, ["id" => $id]);
    }

    public function getAllByUserIdPaginated(string $user_id, int $limit = 3, int $offset = 0)
    {
        if (!$user_id) {
            return null;
        }

        $sql = "SELECT * FROM todos WHERE user_id = :user_id LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create(int $user_id, string $description)
    {
        $sql = "INSERT INTO todos (user_id, description) VALUES (:user_id, :description)";
        $this->db->executeUpdate($sql, [
            "user_id" => $user_id,
            "description" => $description
        ]);
    }

    public function update(int $id, int $user_id, int $is_completed)
    {
        $sql = "UPDATE todos SET is_completed = '$is_completed'
                WHERE id = :id AND user_id = :user_id";
        $this->db->executeUpdate($sql, [
            "id" => $id,
            "user_id" => $user_id
        ]);
    }

    public function destroyById(int $id, int $user_id)
    {
        $sql = "DELETE FROM todos WHERE id = :id AND user_id = :user_id";
        $this->db->executeUpdate($sql, [
            "id" => $id,
            "user_id" => $user_id
        ]);
    }

    public function countByUserId(int $user_id)
    {
        $sql = "SELECT COUNT(*) FROM todos WHERE user_id = :user_id";

        return $this->db->fetchColumn($sql, ["user_id" => $user_id]);
    }
}
