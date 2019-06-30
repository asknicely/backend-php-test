<?php

use Model\Todo;
use Model\User;
use Symfony\Component\HttpFoundation\Request;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
        'user' => "",
    ]);
});

$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    if ($username) {
        $user = User::exists($app['db'], $username, $password);

        if ($user) {
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        } else {
            $error = "Sorry, wrong password!";
            return $app['twig']->render('login.html', array('error' => $error));
        }
    }
    return $app['twig']->render('login.html', array('error' => "", 'user' => ""));
});

$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

$app->post('/todo/{id}/check', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $completed = $request->get('completed');
    if ($completed === null) {
        $completed = false;
    }

    if ($id) {
        $res = Todo::update($app['db'], $id, ['completed' => $completed]);
        $response = array();
        if ($res) {
            $response = array(
                'status' => 'success',
                'message' => "status changed.",
                'completed' => $completed,
            );
        } else {
            $response = array(
                'status' => 'failed',
                'message' => "error happened.",
                'error_code' => $res,
            );
        }
    }
    return $app->json($response);
});

$app->get(
    '/todo/{id}/json',
    function ($id, Request $request) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }
        $response = array();
        if ($id) {
            $todo = Todo::find($app['db'], $id);
            if ($todo and $todo['user_id'] == $user['id']) {
                $response = array(
                    'id' => $todo['id'],
                    'user_id' => $todo['user_id'],
                    'description' => $todo['description'],
                    'completed' => $todo['completed'],
                );
            } else {
                $response = array(
                    'error' => 'Invalid Todo ID',
                );
            }
        } else {
            $response = array(
                'error' => 'Invalid Todo ID',
            );
        }
        return $app->json($response);
    }
);

$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $todo = Todo::find($app['db'], $id);
        if ($todo and $todo['user_id'] == $user['id']) {
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        } else {
            $error = "The todo doesn't exist!";
            $todos = User::todos($app['db'], $user['id']);
            return $app['twig']->render('todos.html', [
                'todos' => $todos,
                'error' => $error,
            ]);
        }
    } else {
        // If there is an error of code 1, pass error message to template.
        $todos = User::todos($app['db'], $user['id']);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'error' => "",
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

    // Validate description
    if ($description === "") {
        $app['session']->getFlashBag()->add('error', 'Failed: You must type in a description!');
        return $app->redirect('/todo');
    }

    $res = Todo::add($app['db'], $user_id, $description, 0);
    if ($res) {
        $app['session']->getFlashBag()->add('confirmation', 'Added a Todo! ');
    } else {
        $app['session']->getFlashBag()->add('error', 'Add Failed! ');
    }
    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];

    $res = Todo::delete($app['db'], $id, ['user_id' => $user_id]);

    if ($res) {
        $app['session']->getFlashBag()->add('confirmation', 'Deleted a Todo! ');
    } else {
        $app['session']->getFlashBag()->add('error', 'Delete Failed! ');
    }
    return $app->redirect('/todo');

});
