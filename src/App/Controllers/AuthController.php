<?php

namespace App\Controllers;

use App\Entities\User;

/**
 * Class AuthController
 *
 * @package App\Controllers
 */
class AuthController extends Common
{
    /**
     * Check if user is not null. Used for Auth
     *
     * @return bool
     */
    public function isAuth()
    {
        return (null !== $this->_user);
    }

    /**
     * Attempt to authenticate user with provided username and password
     *
     * @param $username
     * @param $password
     * @return bool
     */
    public function login($username, $password)
    {
        $_uname = trim($username);
        $_pass = trim($password);

        if ($_uname !== '' && $_pass != '') {
            if ($user = $this->_em->getRepository('\App\Entities\User')->findBy(['username' => $username, 'password' => $password])) {

                if(count($user) > 0 && $user[0] instanceof User){
                    $this->_app['session']->set('user', $user[0]->toArray());

                    return true;
                }
            }

        }

        return false;
    }

    /**
     * Clear session for logout
     */
    public function logout()
    {
        $this->_app['session']->set('user', null);
    }
}