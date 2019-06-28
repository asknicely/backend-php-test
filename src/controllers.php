<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


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


$app->post('/todo/{id}/check', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $completed = $request->get('completed');
    if ($completed === NULL) $completed = False;
    if ($id) {
        $sql = "UPDATE todos SET completed = $completed WHERE id = '$id'";
        $app['db']->executeUpdate($sql);
        $response = array(
            'status' => 'success',
            'message' => "status changed.",
            'completed' => $completed,
        );
        return $app->json($response);
    }
});

$app->get(
    '/todo/{id}/json',
    function ($id, Request $request) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }
        if ($id) {
            $sql = "SELECT * FROM todos WHERE id = '$id'";
            $todo = $app['db']->fetchAssoc($sql);
            if ($todo) {
                $response = array(
                    'id' => $todo['id'],
                    'user_id' => $todo['user_id'],
                    'description' => $todo['description'],
                    'completed' => $todo['completed'],
                );
            } else {
                $response = array(
                    'error' => 'Invalid Todo ID',
                );
            }
            return $app->json($response);
        }
    }
);

$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
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
        // If there is an error of code 1, pass error message to template.
        $error = "";
        if ($request->get('error') == 1) {
            $error = "Failed: You must type in a description!";
        }
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'error' => $error,
        ]);
    }
})
    ->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // Validate description
    if ($description === "") {
        return $app->redirect('/todo?error=1');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});
