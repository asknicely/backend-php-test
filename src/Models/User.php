<?php

namespace Models;

class User
{

    public function __construct()
    {
    }

    public function getUserByUsernameAndPass($data)
    {
        return $this->queryBuilder
            ->select('id', 'username', 'password')
            ->from('users')
            ->where('username = ?')
            ->andWhere('password = ?')
            ->setParameter(0, $data['username'])
            ->setParameter(1, $data['password'])
            ->execute()
            ->fetch();
    }
}