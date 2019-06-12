<?php
namespace App\Controllers;

use App\Models\UserModel;
use Symfony\Component\Validator\Constraints as Assert;

class UserController
{
    private $app;
    private $model;
    function __construct($app)
    {
        $this->app = $app;
        $this->model = new UserModel($app['db']);
    }

    // inject mock model for test
    public function setModel($model)
    {
        $this->model = $model;
    }

    public function login($usrName, $pwd)
    {
        $usrName = trim($usrName);
        $pwd = trim($pwd);
        $nameErr = $this->app['validator']->validate($usrName, new Assert\NotBlank());
        $pwdErr =  $this->app['validator']->validate($pwd, new Assert\NotBlank());
        if (!count($nameErr) && !count($pwdErr) && false !== $user = $this->model->verify($usrName, $pwd)) {
            $this->app['session']->set('user', $user);
            return $this->app->redirect('/todo');
        }
        return $this->app['twig']->render('login.html');
    }

    public function logout()
    {
        $this->app['session']->set('user', null);
        return $this->app->redirect('/');
    }
}
