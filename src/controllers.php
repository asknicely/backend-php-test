<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});

$app->get('/test', function () use ($app) {

    return $app['twig']->render('test.html');

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


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);

/**
 * Controller for 'Adding List items' per the user's requrest.
 * Validation on the 'description' input field occurs.
 */
$app->post('/todo/add', function (Request $request) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Define Usable Variables
    $user_id        = $user['id'];
    $description    = trim($request->get('description'));

    // Validate if the $description variable has a value or is empty
    if($description != ''){

        // If there is a value, insert the new 'item' into the database.
        $sql        = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        return $app->redirect('/todo');

    } else {

        // If there is no value, redirect user with FlashBag message, asking them to add a description.
        $app['session']->getFlashBag()->set('empty_description', 'Please add a description..');
        return $app->redirect('/todo');
    }

});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});