<?php

use Symfony\Component\HttpFoundation\Request;
use Controllers\TodoController;
use Controllers\UserController;

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

// USER Routes
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
$app->get('/todo', function ($id, Request $request) use ($app) {
    $todo = new TodoController($app);
    return $todo->getAll($request);
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

$app->match('/todo/{id}/json', function ($id) use ($app) {
    $todo = new TodoController($app);
    return $todo->getOneJSON($id);
})->before($loginCheck);

$app->match('/todo/{id}/complete', function ($id, Request $request) use ($app) {
    $todo = new TodoController($app);
    return $todo->completeTodo($id, $request);
})->before($loginCheck);