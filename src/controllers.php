<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array(
        'readme' => file_get_contents('../README.md')
    ));
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
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

$app->get('/todos/{pid}', function ($pid) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if (is_numeric($pid) && is_int((int)$pid)) {
        $pid = (int)$pid;
    } else {
        $pid = 1;
    }

    $itemsPerPage = 10;
    $offset = 10 * ($pid - 1);

    $totalCountSQL = "SELECT count(*) FROM todos WHERE user_id = '${user['id']}'";
    $totalCount = (int)$app['db']->fetchColumn($totalCountSQL);

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT {$itemsPerPage} OFFSET {$offset}";
    $todos = $app['db']->fetchAll($sql);

    $pagination = array(
        'totalCount' => $totalCount,
        'totalPages' => ceil($totalCount / $itemsPerPage),
        'currentPage' => $pid,
        'currentPageItems' => count($todos),
        'itemsPerPage' => $itemsPerPage
    );

    return $app['twig']->render('todos.html', array(
        'todos' => $todos,
        'pagination' => $pagination
    ));

})->value('pid', '1');

$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', array(
            'todo' => $todo
        ));
    } else {
        return $app->redirect('/todos');
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);
        $todo['json_format'] = json_encode($todo);

        return $app['twig']->render('todo-json.html', array(
            'todo' => $todo
        ));
    } else {
        return $app->redirect('/todos');
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = trim($request->get('description'));

    // if not empty string
    if (!empty($description)) {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $execResult = $app['db']->executeUpdate($sql);

        if ($execResult === 1) {
            $app['session']->getFlashBag()->add('message', array('type' => 'success', 'content' => "Todo {$description} was successfully added."));
        } else {
            $app['session']->getFlashBag()->add('message', array('type' => 'danger', 'content' => "Todo {$description} failed to add."));
        }
    }

    return $app->redirect('/todos');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $execResult = $app['db']->executeUpdate($sql);

    if ($execResult === 1) {
        $app['session']->getFlashBag()->add('message', array('type' => 'success', 'content' => "Todo #{$id} was successfully deleted."));
    } else {
        $app['session']->getFlashBag()->add('message', array('type' => 'danger', 'content' => "Todo #{$id} failed to delete."));
    }

    return $app->redirect('/todos');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed = '1' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todos');
});

$app->match('/todo/undo/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed = '0' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todos');
});