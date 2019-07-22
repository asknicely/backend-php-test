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
        'readme' => file_get_contents('../README.md'),
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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
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
        $_limit = 10;
        $page = $request->get('page', 1);
        $_offset = $_limit * ($page - 1);


        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT $_limit OFFSET $_offset";
        $todos = $app['db']->fetchAll($sql);

        $sql = "SELECT COUNT(*) as cnt FROM todos WHERE user_id = '${user['id']}'";
        $count = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'pagination' => [
                'total' => ceil($count['cnt'] / $_limit),
                'current' => $page,
            ]
        ]);
    }
})->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    if ($todo) {
        return $app->json($todo);
    } else {
        return $app->json([]);
    }
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) === 0) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('alert', 'TODO task added successfully.');

        $sql = "SELECT COUNT(*) as cnt FROM todos WHERE user_id = '${user['id']}'";
        $count = $app['db']->fetchAssoc($sql);

        return $app->redirect('/todo?page=' . ceil($count['cnt'] / 10));
    } else {
        $app['session']->getFlashBag()->add('error', 'Please add a description.');
    }

    return $app->redirect($request->headers->get('referer'));
});

$app->put('/todo/complete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->abort(401);
    }

    $user_id = $user['id'];

    $sql = "UPDATE todos SET completed=1 WHERE id='$id' AND user_id='$user_id'";
    $app['db']->executeUpdate($sql);

    return $app->json(['html' => '<div class="alert alert-success">TODO task completed successfully.</div>']);
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('alert', 'TODO task deleted successfully.');

    return $app->redirect('/todo');
});