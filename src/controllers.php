<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// auth check
$authMiddleware = function (Request $request, $app) {
    $user = $app['session']->get('user');
    if (empty($user)) {
        return $app->redirect('/login');
    }
};

// api endpoints

// get todos
$app->get('/api/v1/todo', function () use ($app) {
    $user    = $app['session']->get('user');
    $user_id = $user['id'];

        $sql = "SELECT todos.*, users.username FROM todos 
            INNER JOIN users ON todos.user_id = users.id 
            WHERE user_id = '$user_id'";
        $todos = $app['db']->fetchAll($sql);

        return $app->json($todos, Response::HTTP_OK);
})
->before($authMiddleware);

// get a s cpecific
$app->get('/api/v1/todo/{id}', function (int $id) use ($app) {
    $user    = $app['session']->get('user');
    $user_id = $user['id'];

    $sql = "SELECT todos.*, users.username FROM todos 
        INNER JOIN users ON todos.user_id = users.id 
        WHERE todos.id='$id'AND user_id = '$user_id'";
    $todo = $app['db']->fetchAssoc($sql);

    return $app->json($todo, Response::HTTP_OK);
})
->before($authMiddleware);

// add a todo
$app->post('/api/v1/todo/add', function (Request $request) use ($app) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data        = json_decode($request->getContent(), true);
        $description = $data['description'] ?? null;
        $user        = $app['session']->get('user');
        $user_id     = $user['id'];
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);

        return $app->json([], Response::HTTP_OK);
    }

    return $app->json([], Response::HTTP_BAD_REQUEST);
})
->before($authMiddleware);

// delete a todo
$app->delete('/api/v1/todo/{id}', function (int $id) use ($app) {
    $user    = $app['session']->get('user');
    $user_id = $user['id'];

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    return $app->json([], Response::HTTP_OK);
})->before($authMiddleware);


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
})
->before($authMiddleware);


$app->get('/todo/{id}', function (?int $id) use ($app) {
    $user    = $app['session']->get('user');
    $user_id = $user['id'];

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '$user_id'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null)
->before($authMiddleware);