<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

const COMPLETED = 1;
const PROCESSING = 0;
const PER_PAGE = 5;

// Allow to get put method
Request::enableHttpMethodParameterOverride();

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
        $currentPage = $request->get('page') ?? 1;
        $offest = ($currentPage - 1) * PER_PAGE;

        $user_id = $user['id'];

        // get total page number
        $sql = "SELECT COUNT(*) AS cnt FROM todos WHERE user_id = '$user_id'";
        $count = $app['db']->fetchAssoc($sql)['cnt'];
        $maxPages = ceil($count / PER_PAGE);

        // get todo list on currecnt page
        $limit = PER_PAGE;
        $sql = "SELECT * FROM todos WHERE user_id = '$user_id' LIMIT $offest, $limit";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render(
            'todos.html',
            compact(
                'todos',
                'maxPages',
                'currentPage'
            )
        );
    }
})->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if (!$id) {
        return $app->json([
            'error' => 'Invalid ID.'
        ]);
    }

    $user_id = $user['id'];
    $sql = "SELECT id, user_id, description FROM todos WHERE id = '$id' AND user_id = '$user_id' Limit 1";
    $todos = $app['db']->fetchAssoc($sql);

    if (!$todos) {
        return $app->json([
            'error' => 'Unable to find any result.'
        ]);
    }

    return $app->json($todos);
})->assert('id', '\d+');

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = trim($request->get('description'));

    // Validate rule for description
    $errors = $app['validator']->validate($description, new Assert\NotBlank());
    if (count($errors) > 0) {
        $app['session']->getFlashBag()->add('alerts', ['type' => 'danger', 'message' => "Can't add a task without a description."]);
        return $app->redirect('/todo');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('alerts', ['type' => 'success', 'message' => 'Added successfully.']);
    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('alerts', ['type' => 'success', 'message' => 'Rmoved successfully.']);
    return $app->redirect('/todo');
});

$app->put('/todo/complete/{id}', function (Request $request, $id) use ($app) {
    // TODO:: Refactory helper or middleware for checking session
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Switch status
    $taskStatus = $request->get('status');
    $taskStatus = $taskStatus == COMPLETED
        ? PROCESSING : COMPLETED;

    $user_id = $user['id'];
    $sql = "UPDATE todos SET status = '$taskStatus' WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('alerts', ['type' => 'success', 'message' => 'Completed task.']);
    return $app->redirect('/todo');
});
