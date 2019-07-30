<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

const COMPLETED = 1;
const PROCESSING = 0;

Request::enableHttpMethodParameterOverride();

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


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
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
})->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if (!$id) {
        return $app->json([
            'error' => 'Invalid ID.'
        ]);
    }

    $user_id = $user['id'];
    $sql = "SELECT id, user_id, description FROM todos WHERE id = '$id' AND user_id = '$user_id' Limit 1";
    $todos = $app['db']->fetchAssoc($sql);

    if (!$todos) {
        return $app->json([
            'error' => 'Unable to find any result.'
        ]);
    }

    return $app->json($todos);
})->assert('id', '\d+');

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = trim($request->get('description'));

    // validate rule for description
    $errors = $app['validator']->validate($description, new Assert\NotBlank());
    if (count($errors) > 0) {
        return $app->abort(400, "Can't add a task without a description.");
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});

$app->put('/todo/complete/{id}', function (Request $request, $id) use ($app) {
    // TODO:: Refactory helper or middleware for checking session
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Switch status
    $taskStatus = $request->get('status');
    $taskStatus = $taskStatus == COMPLETED
        ? PROCESSING : COMPLETED;

    $user_id = $user['id'];
    $sql = "UPDATE todos SET status = '$taskStatus' WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});
