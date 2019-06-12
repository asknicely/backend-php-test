<?php

use Symfony\Component\HttpFoundation\Request;
use App\Controllers\TodoController;
use App\Controllers\UserController;

$app['controller.todo'] = function () use ($app) {
    return new TodoController($app);
};
$app['controller.user'] = function () use ($app) {
    return new UserController($app);
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
    $username = $request->get('username');
    $password = $request->get('password');

    return $app['controller.user']->login($username, $password);
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function (Request $request,  $id) use ($app) {
    if ($id) {
        return $app['controller.todo']->get($id);
    } else {
        $pageNum =  $request->get('pageNum') ?: 1;
        $pageSize = $request->get('pageSize') ?: 5;
        return $app['controller.todo']->getByUserIdWithPagination($pageNum, $pageSize);
    }
})->value('id', null)->assert('id', '\d*');


$app->post('/todo/add', function (Request $request) use ($app) {
    $description = $request->get('description');
    return $app['controller.todo']->add($description);
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    return $app['controller.todo']->delete($id);
})->assert('id', '\d+');

$app->post('/todo/complete/{id}', function ($id) use ($app) {
    return $app['controller.todo']->toggleComplete($id);
})->assert('id', '\d+');

$app->get('/todo/json/{id}', function ($id) use ($app) {
    return $app['controller.todo']->getJson($id);
})->assert('id', '\d+');
