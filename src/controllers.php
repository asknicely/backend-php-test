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
            return $app->redirect('/todos');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

$app->get('/todos/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $items_per_page = 5;
    $page = (int)$page;

    // Make sure min page is 1
    $page = $page <= 0 ? 1 : $page;

    $items_offset = $items_per_page * ($page - 1);

    $user_id = $user['id'];

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT $items_per_page OFFSET $items_offset";
    $todos = $app['db']->fetchAll($sql);

    $pages_available = $app['db']->fetchArray("SELECT CEILING(COUNT(*) / $items_per_page) FROM todos WHERE user_id = '${user['id']}'")[0];

    return $app['twig']->render('todos.html', [
        'todos'           => $todos,
        'pages_available' => $pages_available,
        'page'            => $page,
    ]);

})->value('page', 1);


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];

    $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $todo = $app['db']->fetchAssoc($sql);

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);
})
    ->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];

    $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $todo = $app['db']->fetchAssoc($sql);

    if (!$todo) {
        return $app->json([
            'status' => 'Not Found'
        ], 404);
    }

    return $app->json($todo);
})
    ->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // Validate the input, trim to make sure no empty spaces are present
    if (trim($description) == "") {
        $app['session']->getFlashBag()->set('todo_error', 'Please input a description to create a todo ;)');
    } else {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->query($sql);
        $app['session']->getFlashBag()->set('todo_add', 'Added new todo');
    }

    return $app->redirect('/todos');
});

$app->post('/todo/changeCompleteStatus/{id}', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $id = $request->get('id');

    $is_completed = isset($_POST['is_completed']) ? 1 : 0;

    $sql = "UPDATE todos SET is_completed = {$is_completed} WHERE id = '{$id}' AND user_id = '{$user_id}'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo/' . $id);
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->set('todo_delete', 'Todo deleted');

    return $app->redirect('/todos');
});