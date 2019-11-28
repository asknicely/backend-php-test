<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $number_of_todos = sizeof($todos);

        $number_of_tasks_per_page = 3;
        $side_buttons_limit = 2;

        $number_of_pages = ceil($number_of_todos / $number_of_tasks_per_page);

        if (isset($_GET["current_page"])){
            $current_page = $_GET["current_page"];
        } else {
            $current_page = 1;
        }

        $pagination_details = array(
            "number_of_pages" => $number_of_pages,
            "current_page" => $current_page,
            "side_buttons_limit" => $side_buttons_limit
        );

        $todos_for_current_page = array_slice($todos, $number_of_tasks_per_page * ($current_page - 1), $number_of_tasks_per_page);

        return $app['twig']->render('todos.html', [
            'todos' => $todos_for_current_page,
            'pagination_details' =>  $pagination_details
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

    if($description){
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('message', 'Task Added');
    } else {
        $app['session']->getFlashBag()->add('message', 'Task needs a Description');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('message', 'Task Deleted');
    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $sql = "UPDATE todos SET completed = TRUE WHERE id='$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('message', 'Task set as Completed');

    return $app->redirect('/todo');
});

$app->match('/todo/uncomplete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $sql = "UPDATE todos SET completed = FALSE WHERE id='$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('message', 'Task set as Uncompleted');

    return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return json_encode($todo);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return json_encode($todos);
    }
});