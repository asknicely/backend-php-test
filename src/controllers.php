<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if (!$id) {
        $error = ['message' => 'Missing ID'];

        return $app->json($error, 400);
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    if (!$todo) {
        $error = ['message' => 'No todo was found.'];

        return $app->json($error, 404);
    }

    return $app->json($todo);
});

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) > 0) {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'errors' => $errors,
        ]);
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'New todo was created successfully');

    return $app->redirect('/todo');
});

$app->patch("/todo/{id}", function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $is_completed = $request->get("is_completed") ? 1 : 0;

    $sql = "UPDATE todos SET is_completed = '$is_completed' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', 'Todo was deleted successfully');
    return $app->redirect('/todo');
});
