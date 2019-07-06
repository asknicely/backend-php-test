<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Controllers\TodoController;
use Controllers\UserController;

//  todo IMPORT CONTROLLER AND MODEL

//  todo CALL THIS FILE ROUTES

//  todo MOVE ALL LOGIC TO CONTROLLERS AND MODELS


// Middleware for checking if user is logged in
$loginCheck = function (Request $request, $app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/');
    }
};

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));
    return $twig;
}));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});

$app->match('/login', function (Request $request) use ($app) {
    if($request->get('username') && $request->get('password')){
        $user = new UserController($app);
        $user->login($request);
        return $app->redirect('/todo');
    }

    return $app['twig']->render('login.html', array());
});

$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

// TODO Routes
$app->get('/todo', function ($id) use ($app) {
    $todo = new TodoController($app);
    return $todo->getAll();
})->value('id', null)->before($loginCheck);

$app->get('/todo/{id}', function ($id) use ($app) {
    $todo = new TodoController($app);
    return $todo->getOne($id);
})->before($loginCheck);

$app->post('/todo/add', function (Request $request) use ($app) {
    $todo = new TodoController($app);
    return $todo->addTodo($request);
})->before($loginCheck);

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $todo = new TodoController($app);
    return $todo->deleteTodo($id);
})->before($loginCheck);