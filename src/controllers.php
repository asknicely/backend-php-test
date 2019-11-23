<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$app['controller.todo'] = function () use ($app) {
    return new \Todo\TodoController(
        $app
    );
};

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents(dirname(__FILE__).'/../README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $query = $app['db.builder']->select('*')->from('users')->where('username =?')->andWhere('password=?')
            ->setParameter(0, $username)->setParameter(1, $password);;
        $user = $query->execute()->fetchAll();
        if (isset($user[0])) {
            $app['session']->set('user', $user[0]);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

$app->get('/todo/{id}/{type}', "controller.todo:get")
    ->value('id', null)->value('type',null);
//add task
$app->post('/todo/add', 'controller.todo:add');

//complete the task
$app->post('/todo/completed/{id}', "controller.todo:completed");
//delete task
$app->match('/todo/delete/{id}', "controller.todo:delete");