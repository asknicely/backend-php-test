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

        $sql = "SELECT * FROM users WHERE username = :username and password = :password";
        return $this->db->fetchAssoc($sql, [
            "username" => $name,
            "password" => $password
        ]);
    }
}
