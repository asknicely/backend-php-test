<?php

use App\Model\TodoModel;
use App\Model\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$app['todomodel'] = new TodoModel($app['db']);

$app['usermodel'] = new UserModel($app['db']);
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

    if ($username && $password) {
        $user = $app['usermodel']->checkLogin($username, $password);

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


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id) {
        $todo = $app['todomodel']->get($id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $user_id = $user['id'];
        $limit = (int)$request->get('limit', 5);
        $page = (int)$request->get('page', 1);
        $offset = ($page - 1) * $limit;

        $count = $app['todomodel']->getCount($user_id);

        $todos = $app['todomodel']->getAllbyUser($user_id, $offset, $limit);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'onPage' => $page,
            'maxPages' => ceil($count / $limit),
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

    //validate description field
    $errors = $app['validator']->validate($description, new Assert\NotBlank());
    if (count($errors) == 0) {
        $app['todomodel']->add($user_id, $description);
        $app['session']->getFlashBag()->add('notice', 'Added todo successfully!');
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $app['todomodel']->delete($id);
    $app['session']->getFlashBag()->add('notice', 'Deleted todo successfully!');

    return $app->redirect('/todo');
});

$app->post('/todo/complete/{id}', function ($id) use ($app) {
    $app['todomodel']->setAsCompleted($id);
    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $todo = $app['todomodel']->get($id);
    return $app['twig']->render('todo_json.html', [
        'id' => $id,
        'todo' => json_encode($todo)
    ]);
})->value('id', null);