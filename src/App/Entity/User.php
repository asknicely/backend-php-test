<?php

namespace App\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class User
{
    /**
     * Password for the user
     * @var string
     *
     */
    private $password;

    /**
     * Username for the user
     * @var string
     *
     */
    private $username;

    /**
     * @param string $username
     * @param string $password
     *
     * @return object, $user
     */
    public function getUserByCredentials($app, $username, $password)
    {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);
        return $user;
    }
}