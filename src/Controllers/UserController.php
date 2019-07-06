<?php

namespace Controllers;

use Models\User;

class UserController extends User
{

    public function __construct($app)
    {
        $this->app = $app;
        $this->queryBuilder = $app['db']->createQueryBuilder();
    }

    /**
     * Getting all todos from current user that is logged in
     */
    public function login($request)
    {
        $data = [
            'username' => $request->get('username'),
            'password' => $request->get('password')
        ];

        // Double check, and additional validation
        if ($data['username'] && $data['password'] && strlen($data['username']) >= 4 && strlen($data['password']) >= 4) {
            $user = User::getUserByUsernameAndPass($data);

            if ($user) {
                $this->app['session']->set('user', $user);
            }
        }
    }

}