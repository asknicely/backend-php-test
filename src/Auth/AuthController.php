<?php

namespace Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AuthController
{

    public $app;

    public function __construct($app)
    {
        $this->app = $app;

    }

    /*
     * try to use symfony security packages failed due to package not main anymore
     * using php internal password password_hash install
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        // password need a encription
        if ($username) {
            $query = $this->app['db.builder']->select('*')->from('users')->where('username =?')->setParameter(0, $username);
            $user = $query->execute()->fetchObject();
            if ($user) {
                if (password_verify($password, $user->password)) {
                    $this->app['session']->set('user', $user);
                    return $this->app->redirect('/todo');
                }
            } else {
                $this->app['session']->getFlashBag()->add('Failed', 'Failed: User not found');
                return $this->app['twig']->render('login.html', array());
            }
        }

        return $this->app['twig']->render('login.html', array());
    }

    public function logout()
    {
        $this->app['session']->set('user', null);
        return $this->app->redirect('/');
    }
}
