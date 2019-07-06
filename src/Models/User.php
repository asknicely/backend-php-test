<?php

namespace Models;

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

class User
{

    /**
     * Getting user by username and password (hashed)
     */
    public function getUserByUsernameAndPass($data)
    {
        $hash = $this->passwordEncode($data['password']);

        return $this->queryBuilder
            ->select('id', 'username', 'password')
            ->from('users')
            ->where('username = ?')
            ->andWhere('password = ?')
            ->setParameter(0, $data['username'])
            ->setParameter(1, $hash)
            ->execute()
            ->fetch();
    }

    /**
     * Getting encoded password from string value
     */
    private function passwordEncode($pass)
    {
        $encoder = new BCryptPasswordEncoder(13);
        return $encoder->encodePassword($pass, 'There is no spoon! All your base belong to us!');
    }
}