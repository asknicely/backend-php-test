<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../config/constants.php';
require_once __DIR__.'/Utils.php';
require_once __DIR__.'/Model.php';

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
        $model = new Model($app['db'], "users");
        $user = $model->findOne(['username'=>$username, 'password'=>$password]);

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


$app->get('/detail/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $id = intval($id);
    $model = new Model($app['db'], "todos");
    $todo = $model->findOne(['id'=>$id, 'user_id'=>$user['id']]);
    return $app['twig']->render('todo.html', [
        'todo' => $todo,
    ]);

})
    ->value('id', null);

$app->get('/todo/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $size = 5;
    $page = intval($page);
    $page = $page > 0 ? $page : 1;
    $offset = ($page - 1) * $size;

    $model = new Model($app['db'], "todos");
    $todos = $model->findAll(['user_id'=>$user['id']], " LIMIT {$offset}, {$size}");
    $rowCount = $model->rowCount(['user_id'=>$user['id']]);
    $pageCount= ceil($rowCount / $size);

    return $app['twig']->render('todos.html', [
        'page_no'    => $page,
        'page_count' => $pageCount,
        'todos'      => $todos,
    ]);
})
    ->value('page', null);



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
        $model = new Model($app['db'], "todos");
        $cnt = $model->insertOne(['user_id'=>intval($user_id), 'description'=>$description]);
        if ($cnt === 1) {
            $app['session']->getFlashBag()->add('success', 'Todo added successfully.');
        } else {
            $app['session']->getFlashBag()->add('notice', 'Wopps, something went wrong.');
        }
        return $app->redirect('/todo');
    });


    $app->match('/todo/delete/{id}', function ($id) use ($app) {

        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $id = intval($id);

        $model = new Model($app['db'], "todos");
        $cnt = $model->deleteOne(['id'=>$id, 'user_id'=>$user['id']]);


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

        $model = new Model($app['db'], "todos");
        $model->updateAll(['is_completed'=>$completed], ['id'=>$id, 'user_id'=>intval($user_id)]);

        return $app->redirect('/todo');
    });


    $app->match('/todo/{id}/json', function ($id) use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $user_id = $user['id'];

        $model = new Model($app['db'], "todos");
        $todo = $model->findOne(['id'=>$id, 'user_id'=>$user_id]);

        $json = '';
        if ($todo) {
            $todo['id'] = intval($todo['id']);
            $todo['user_id'] = intval($todo['user_id']);
            $json = Utils::createJson($todo);
        }
        return $json;
    });
