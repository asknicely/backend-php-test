<?php

namespace App\Model;

class UserModel
{
    protected $db;
    const TABLE = 'users';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function checkLogin($username, $password)
    {
        // check login details record in table
        return $this->db->fetchAssoc('SELECT * FROM ' . self::TABLE . ' WHERE username = ? AND password = ?', array($username, $password));
    }

}