<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../config/constants.php';
require_once __DIR__.'/utils.php';

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
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

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

	$id = intval($id);

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


$app->post('/todo/add', function (Request $request) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];

    $description = $request->get('description');
    if (empty(trim($description))) {
		$app['session']->getFlashBag()->add('notice', 'Description is necessary.');
        return $app->redirect('/todo');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
    $app['db']->executeUpdate($sql, [$user_id, $description]);

	$app['session']->getFlashBag()->add('success', 'Todo added successfully.');
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

	$id = intval($id);
    $sql = "DELETE FROM todos WHERE id = '$id'";
    $cnt = $app['db']->executeUpdate($sql);
	if ($cnt === 1) {
		$app['session']->getFlashBag()->add('success', 'A todo was deleted successfully.');
	} else {
		$app['session']->getFlashBag()->add('notice', 'Woops, something went wrong, please try again.');
	}
    return $app->redirect('/todo');
});

$app->match('/todo/markComplete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

	$id = intval($id);
    $user_id = $user['id'];

    $completed = TODO_IS_COMPLETED;

    $sql = "UPDATE todos SET is_completed = {$completed} WHERE id = '{$id}' AND user_id = '{$user_id}'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];

    $sql  = "SELECT id, user_id, description FROM todos WHERE id = ? AND user_id = ?";
    $todo = $app['db']->fetchAssoc($sql, [$id, $user_id]);

    $json = '';
    if ($todo) {
        $todo['id'] = intval($todo['id']);
        $todo['user_id'] = intval($todo['user_id']);
        $json = createJson($todo);
    }
    return $json;

});