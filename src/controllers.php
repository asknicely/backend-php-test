<?php

use Symfony\Component\HttpFoundation\Request;
use App\Controllers\AuthController;
use App\Controllers\TaskController;


/* BASIC MIDDLEWARE */
// Check if user is already logged in, and if true redirect to task list
$guest = function (Request $request, $app) {
    if ((new \App\Controllers\AuthController($app))->isAuth()) return $app->redirect('/todo');
};

// Check is user is not logged in, if true redirect to login page
$validateAuth = function (Request $request, $app) {
    if (!(new \App\Controllers\AuthController($app))->isAuth()) return $app->redirect('/login');
};

// Return 401 Unathorized for ajax requests where user is not logged in
$validateAuthAjax = function (Request $request, $app) {
    if (!(new \App\Controllers\AuthController($app))->isAuth()) return $app->abort(401);
};
/* END MIDDLEWARE */


$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $ac = new AuthController($app);

    if ($ac->login($request->get('username'), $request->get('password'))) {
        return $app->redirect('/todo');
    }

    if ($request->isMethod('post')) $app['session']->getFlashBag()->add('error', 'Invalid username or password.');

    return $app['twig']->render('login.html', array());
})->before($guest);


$app->get('/logout', function () use ($app) {
    $ac = new AuthController($app);
    $ac->logout();

    return $app->redirect('/');
});


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    $tk = new TaskController($app);

    if ($id) {
        $task = $tk->getTask($id);

        if ($task) {
            return $app['twig']->render('todo.html', [
                'todo' => $task,
            ]);
        }

        return $app->redirect('/todo');
    } else {
        $page = $request->get('page', 1);

        return $app['twig']->render('todos.html', [
            'todos' => $tk->getTasks($page),
            'pagination' => [
                'total' => $tk->getTotalPages(),
                'current' => $page,
            ]
        ]);
    }
})->value('id', null)->before($validateAuth);


$app->get('/todo/{id}/json', function ($id) use ($app) {
    $tk = new TaskController($app);
    $task = $tk->getTask($id);

    if ($task) {
        return $app->json($task);
    }

    return $app->json([]);
})->before($validateAuth);


$app->post('/todo/add', function (Request $request) use ($app) {
    $tk = new TaskController($app);

    if ($result = $tk->createTask($request->get('description'))) {
        $app['session']->getFlashBag()->add('alert', 'TODO task added successfully.');

        return $app->redirect('/todo?page=' . $tk->getTotalPages());
    } else {
        $app['session']->getFlashBag()->add('error', 'Please add a description.');
    }

    return $app->redirect($request->headers->get('referer'));
})->before($validateAuth);


$app->put('/todo/complete/{id}', function ($id) use ($app) {
    $tk = new TaskController($app);

    if ($tk->completeTask($id)) {
        return $app->json(['html' => '<div class="alert alert-success">TODO task completed successfully.</div>']);
    }

    return $app->abort(401);
})->before($validateAuthAjax);


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $tk = new TaskController($app);

    if ($tk->deleteTask($id)) $app['session']->getFlashBag()->add('alert', 'TODO task deleted successfully.');

    return $app->redirect('/todo');
})->before($validateAuth);