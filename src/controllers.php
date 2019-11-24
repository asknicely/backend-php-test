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
$app['controller.auth'] = function () use ($app) {
    return new \Auth\AuthController(
        $app
    );
};
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents(dirname(__FILE__) . '/../README.md'),
    ]);
});


// forward to User should merge check login
$forwardTodo = function () use ($app) {
    // ...
    if ($app['session']->get('user') !== null)
        return $app->redirect('/todo');
};
//middleware for check login
$checkLogin = function () use ($app) {
    if ($app['session']->get('user') == null)
        return $app->redirect('/login');
};

//All routes
$app->match('/login', 'controller.auth:login')->before($forwardTodo);
$app->get('/logout', 'controller.auth:logout');
$app->get('/todo/{id}/{type}', "controller.todo:get")
    ->value('id', null)->value('type', null)->before($checkLogin);
//add task
$app->post('/todo/add', 'controller.todo:add')->before($checkLogin);

//complete the task
$app->post('/todo/completed/{id}', "controller.todo:completed")->before($checkLogin);
//delete task
$app->match('/todo/delete/{id}', "controller.todo:delete")->before($checkLogin);