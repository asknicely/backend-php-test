<?php

namespace Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


class AuthController
{

    public $app;

    public function __construct($app)
    {
        $this->app = $app;

    }

    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        // password need a encription
        if ($username) {
            $query = $this->app['db.builder']->select('*')->from('users')->where('username =?')->andWhere('password=?')
                ->setParameter(0, $username)->setParameter(1, $password);;
            $user = $query->execute()->fetchAll();
            if (isset($user[0])) {
                $this->app['session']->set('user', $user[0]);
                return $this->app->redirect('/todo');
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
