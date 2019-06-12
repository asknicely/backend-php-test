<?php

use Symfony\Component\HttpFoundation\Request;
use App\Controllers\TodoController;


$app['controller.todo'] = function () use ($app) {
    return new TodoController($app);
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

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user) {
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }
    return $app['twig']->render('login.html', array());
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
})
    ->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    $description = $request->get('description');
    return $app['controller.todo']->add($description);
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    return $app['controller.todo']->delete($id);
});

$app->post('/todo/{id}/complete', function ($id) use ($app) {
    return $app['controller.todo']->toggleComplete($id);
})->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    // if we have a valid id then retreve the data and display it as json
    return $app['controller.todo']->getJson($id);
})->value('id', null);
