<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
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
});


// GET [todo] list or view
$app->get('/todo', function (Request $request) use ($app) {
    // If it is not an AJAX request, we just render the view
    if (!$request->isXmlHttpRequest()) {
        return $app['twig']->render('todos.html');
    }
    
    $user = $app['session']->get('user');
    // Fetch [todo]
    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
    $todos = $app['db']->fetchAll($sql);

    // Http Response
    return new JsonResponse($todos, Response::HTTP_OK);
});

// GET [todo]
$app->get('/todo/{id}', function ($id) use ($app) {
    $user = $app['session']->get('user');

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);
})
->assert('id', '\d+');


// POST [todo]
$app->post('/todo', function (Request $request) use ($app) {
    $user = $app['session']->get('user');

    $user_id = $user['id'];
    $description = $request->get('description');

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


// DELETE [todo]
$app->delete('/todo/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    // Flash message
    $app['session']->getFlashBag()->add('success', 'TODO deleted successfully!');
    
    // Http Response
    return new Response(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
})
->assert('id', '\d+');