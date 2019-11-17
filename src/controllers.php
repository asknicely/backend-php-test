<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Controllers\PostController;
use Controllers\Api\TodoController;
use Doctrine\DBAL\Connection;

// auth check
$authMiddleware = function (Request $request, $app) {
    $user = $app['session']->get('user');
    if (empty($user)) {
        return $app->redirect('/login');
    }
};

/**
 * API endpoints
 */
$app['todos.controller'] = function() use ($app) {
    return new TodoController($app['db'], $app['session']);
};

// get todos
$app->get('/api/v1/todo', "todos.controller:index")
    ->before($authMiddleware);

// get a specific todo
$app->get('/api/v1/todo/{id}', "todos.controller:show")
    ->before($authMiddleware);

// delete a todo
$app->delete('/api/v1/todo/{id}', "todos.controller:delete")
    ->before($authMiddleware);

// add a todo
$app->post('/api/v1/todo/add', "todos.controller:store")
    ->before($authMiddleware);

/**
 * Pages
 */
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
})
->before($authMiddleware);


$app->get('/todo/{id}', function (?int $id) use ($app) {
    $user    = $app['session']->get('user');
    $user_id = $user['id'];

    if ($id){
        return $app['twig']->render('todo.html');
    } else {
        return $app['twig']->render('todos.html');
    }
})
->value('id', null)
->before($authMiddleware);
