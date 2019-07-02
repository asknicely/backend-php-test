<?php

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
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
        $limit = (int) $request->get('limit', 5);
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $limit;

        /** @var Doctrine\DBAL\Connection $db */
        $db = $app['db'];

        $sql = "SELECT count(id) FROM todos WHERE user_id = :userId";
        $count = (int) array_values($db->fetchAll($sql, ['userId' => $user['id']])[0])[0];

        $sql = "SELECT * FROM todos WHERE user_id = :userId LIMIT $offset, $limit";
        $todos = $db->fetchAll($sql, ['userId' => $user['id']]);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'onPage' => $page,
            'maxPages' => ceil($count / $limit),
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = (int)$user['id'];
    $description = strip_tags($request->get('description'));

    if (empty($description)) {
        return $app->redirect('/todo');
    }

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $sql = "INSERT INTO todos (user_id, description) VALUES (:id, :description)";
    $db->executeUpdate($sql, [
        'id' => $user_id,
        'description' => $description,
    ]);

    $app['session']->getFlashBag()->add('notifications', 'Success - Todo added');

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('notifications', 'Success - Todo deleted');

    return $app->redirect('/todo');
});

$app->match('/todo/completed/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = (int)$user['id'];

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $sql = "UPDATE todos SET completed = :completed WHERE id = :id";
    $db->executeUpdate($sql, [
        'id' => $id,
        'completed' => true,
    ]);

    return $app->redirect('/todo');
});

$app->get('todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        /** @var Doctrine\DBAL\Connection $db */
        $db = $app['db'];

        $sql = "SELECT * FROM todos WHERE id = :id";
        $todo = $db->fetchAssoc($sql, ['id' => $id]);

        return json_encode($todo);
    } else {
        return json_encode(new stdClass());
    }
})
    ->value('id', null);