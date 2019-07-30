<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

use App\User;
use App\Todo;

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
        $user = User::where([
            'username' => $username,
            'password' => $password,
        ])->first();

        if ($user) {
            $app['session']->set('user', $user->toArray());
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

    $user_id = $user['id'];
    $user = User::findOrFail($user_id);

    // Show
    if ($id) {
        $todo = $user->todos->find($id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        // List
        $currentPage = $request->get('page') ?? 1;

        $count = $user->todos->count();
        $todos = $user->todos->forPage($currentPage, PER_PAGE);
        $maxPages = ceil($count / PER_PAGE);

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
    $user = User::findOrFail($user_id);
    $todo = $user->todos->find($id);

    if (!$todo) {
        return $app->json([
            'error' => 'Unable to find any result.'
        ]);
    }

    return $app->json($todo);
})->assert('id', '\d+');

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $user = User::findOrFail($user_id);

    $description = trim($request->get('description'));

    // Validate rule for description
    $errors = $app['validator']->validate($description, new Assert\NotBlank());
    if (count($errors) > 0) {
        $app['session']->getFlashBag()->add('alerts', ['type' => 'danger', 'message' => "Can't add a task without a description."]);
        return $app->redirect('/todo');
    }

    $newTodo = new Todo;
    $newTodo->description = $description;
    $newTodo->user()->associate($user);
    $result = $newTodo->save();

    if ($result) {
        $type = "success";
        $message = "Added successfully.";
    } else {
        $type = "danger";
        $message = "Unable to add new task.";
    }

    $app['session']->getFlashBag()->add('alerts', ['type' => $type, 'message' => $message]);
    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $user = User::findOrFail($user_id);
    $todo = $user->todos->find($id);
    $result = $todo->delete();

    if ($result) {
        $type = "success";
        $message = "Rmoved successfully.";
    } else {
        $type = "danger";
        $message = "Unable to remove selected task.";
    }

    $app['session']->getFlashBag()->add('alerts', ['type' => $type, 'message' => $message]);
    return $app->redirect('/todo');
});

$app->put('/todo/complete/{id}', function (Request $request, $id) use ($app) {
    // TODO:: Refactory helper or middleware for checking session
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Switch status
    $user_id = $user['id'];
    $user = User::findOrFail($user_id);
    $todo = $user->todos->find($id);
    $todo->status = !$todo->status;
    $result = $todo->save();

    if ($result) {
        $type = "success";
        $message = "Completed task.";
    } else {
        $type = "danger";
        $message = "Unable to udpate task.";
    }

    $app['session']->getFlashBag()->add('alerts', ['type' => $type, 'message' => $message]);
    return $app->redirect('/todo');
});
