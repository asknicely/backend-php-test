<?php

namespace ORM;

class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getByNameAndPassword($name, $password)
    {
        if (!$name || !$password) {
            return null;
        }

        $sql = "SELECT * FROM users WHERE username = :username";
        $user = $this->db->fetchAssoc($sql, ["username" => $name]);

        return (password_verify($password, $user['password']) ? $user : null);
    }
}
