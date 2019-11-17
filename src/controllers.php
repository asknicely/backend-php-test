<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Controllers\PostController;
use Controllers\AuthController;
use Controllers\Api\TodoController as ApiTodoController;
use Controllers\TodoController;
use Doctrine\DBAL\Connection;

// auth check middleware
$authMiddleware = function (Request $request, $app) {
    $user = $app['session']->get('user');
    if (empty($user)) {
        return $app->redirect('/login');
    }
};

/**
 * API endpoints
 */
$app['todos.api.controller'] = function() use ($app) {
    return new ApiTodoController($app['db'], $app['session']);
};

// get todos
$app->get('/api/v1/todo', "todos.api.controller:index")
    ->before($authMiddleware);

// get a specific todo
$app->get('/api/v1/todo/{id}', "todos.api.controller:show")
    ->before($authMiddleware);

// delete a todo
$app->delete('/api/v1/todo/{id}', "todos.api.controller:delete")
    ->before($authMiddleware);

// add a todo
$app->post('/api/v1/todo/add', "todos.api.controller:store")
    ->before($authMiddleware);


$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


/**
 * Pages
 */

 // homepage
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


/**
 * Auth
 */
$app['auth.controller'] = function() use ($app) {
    return new AuthController($app);
};

// logout
$app->get('/logout', "auth.controller:logout")
    ->before($authMiddleware);

// login form
$app->get('/login', "auth.controller:loginForm");

// login request handler
$app->post('/login', "auth.controller:login");


/**
 * Pages
 */
$app['todos.controller'] = function() use ($app) {
    return new TodoController($app);
};

// Todo List page
$app->get('/todo', "todos.controller:index")
    ->before($authMiddleware);

// Todo page
$app->get('/todo/{id}', "todos.controller:show")
    ->before($authMiddleware);

// get a specific todo (json)
$app->get('/todo/{id}/json', "todos.controller:showJson")
    ->before($authMiddleware);
