<?php

namespace App\Controllers;

use App\Entities\User;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

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
     * Attempt to authenticate user with provided username and password. If user is using plaintext password. Rehash password for future authentication
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
            if ($user = $this->_em->getRepository('\App\Entities\User')->findBy(array('username' => $username))) {

                if (count($user) > 0 && $user[0] instanceof User) {
                    $user = $user[0];

                    $auth_pass = false;

                    if ($user->getHashType() == 'plaintext') {
                        if ($user->getPassword() == $_pass) {
                            $this->hashNewPassword($user);
                            $auth_pass = true;
                        }
                    } elseif ($user->getHashType() == 'bcrypt') {
                        if ($this->compareHash($user, $_pass)) $auth_pass = true;
                    }

                    if ($auth_pass) {
                        $this->_app['session']->set('user', $user->toArray());
                        return true;
                    }
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

    /**
     * Compare a users stored password hash with an encoding of supplied password using the user's salt.
     *
     * @param $user
     * @param $password
     * @return bool
     */
    private function compareHash($user, $password){
        $_encoder = new BCryptPasswordEncoder(10);

        if($user->getPassword() === $_encoder->encodePassword($password, $user->getSalt())) return true;

        return false;
    }

    /**
     * Create a bcrypt hash for the user's password and store it in user record along with salt that was used
     *
     * @param $user
     */
    private function hashNewPassword($user)
    {
        $_salt = $this->getRandomSalt();

        $_encoder = new BCryptPasswordEncoder(10);

        $user->setPassword($_encoder->encodePassword($user->getPassword(), $_salt));
        $user->setSalt($_salt);
        $user->setHashType('bcrypt');

        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Generate a salt containing random characters of a given length.
     *
     * @param int $length
     * @return string
     */
    private function getRandomSalt($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }
}