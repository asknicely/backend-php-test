<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    if(!empty($todo))
    {
        $todotmp = $todo;
        unset($todotmp['status']);

        $todoInJson = json_encode($todotmp);
        return $app['twig']->render('todo.json.html', [
            'todo' => $todo,
            'json' => $todoInJson,
        ]);
    }
    else 
    {
        return $app->redirect('/todo');
    }
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    $description = trim($description);      //trim description first

    //validate description
    $error = $app['validator']->validate($description, new Assert\NotBlank());
    if(count($error) > 0)
    {
        $app['session']->getFlashBag()->add('error', 'Please add a description.');
    }
    else 
    {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('confirmation', 'New TODO added.');

    }

    return $app->redirect('/todo');
});


$app->match('/todo/{id}/delete', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('confirmation', "TODO (id:{$id}) deleted!");


    return $app->redirect('/todo');
});

/**
 * Complete a toto record
 */
$app->match('/todo/{id}/complete', function ($id) use ($app) {

    //add status in ENUM instead of BOOL. So that we can give the record other status, LILE highlight, not start ...
    $sql = "UPDATE `todos` SET `status` = 'completed' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});

/**
 * Undo complete action
 */
$app->match('/todo/{id}/undo', function ($id) use ($app) {

    $sql = "UPDATE `todos` SET `status` = 'processing' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});