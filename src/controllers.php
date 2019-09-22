<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use ORM\User;
use ORM\Todo;

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
        $user = (new User($app['db']))->getByNameAndPassword($username, $password);

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
        $todo = (new Todo($app['db']))->getById($id);

        if (!$todo) {
            return $app->redirect('/todo');
        }

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    }

    $total_todos = (new Todo($app['db']))->countByUserId($user['id']);

    $limit = $request->get('limit');
    $total_pages = ceil($total_todos / $limit);

    $current_page = $request->get('page');

    if ($current_page > $total_pages || $current_page < 0) {
        return $app->redirect('/todo');
    }

    $offset = ($current_page - 1) * $limit;

    $todos = (new Todo($app['db']))->getAllByUserIdPaginated($user['id'], $limit, $offset);

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
    ]);
})
->convert('limit', function ($limit) {
    return (int) $limit;
})
->value('id', null)
->value('limit', 3)
->value('page', 1);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if (!$id) {
        $error = ['message' => 'Missing ID'];

        return $app->json($error, 400);
    }

    $todo = (new Todo($app['db']))->getById($id);

    if (!$todo) {
        $error = ['message' => 'No todo was found.'];

        return $app->json($error, 404);
    }

    return $app->json($todo);
});

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) > 0) {
        $app['session']->getFlashBag()->add('error', 'Please add a description');

        return $app->redirect('/todo');
    }

    (new Todo($app['db']))->create($user_id, $description);

    $app['session']->getFlashBag()->add('success', 'New todo was created successfully');

    return $app->redirect('/todo');
});

$app->patch("/todo/{id}", function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    (new Todo($app['db']))->update($id, $user['id'], $request->get("is_completed") ? 1 : 0);

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    (new Todo($app['db']))->destroyById($id, $user['id']);

    $app['session']->getFlashBag()->add('success', 'Todo was deleted successfully');
    return $app->redirect('/todo');
});
