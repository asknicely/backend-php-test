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
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

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
        $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
        $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = ?";
        $todos = $app['db']->fetchAll($sql, [$user['id']]);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
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

    // validate description
    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) == 0) {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $app['db']->executeUpdate($sql, [$user_id, $description]);
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "DELETE FROM todos WHERE id = ? AND user_id = ?";
    $app['db']->executeUpdate($sql, [$id, $user['id']]);

    return $app->redirect('/todo');
});

// complete a todo
$app->match('/todo/complete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = ?";
    $todo = $app['db']->fetchAssoc($sql, [$id]);

    if ($todo) {
        $sql = "UPDATE todos SET completed_at = ? WHERE id = ? AND user_id = ?";
        $app['db']->executeUpdate($sql,
            [
                (new DateTime())->format('Y-m-d H:i:s'),
                $id,
                $user['id']
            ]);
    }

    return $app->redirect('/todo');
});
