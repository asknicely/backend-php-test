<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('/var/www/README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $query = $app['db.builder']->select('*')->from('users')->where('username =?')->andWhere('password=?')
            ->setParameter(0, $username)->setParameter(1, $password);;
        $user = $query->execute()->fetchAll();
        if (isset($user[0])) {
            $app['session']->set('user', $user[0]);
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
    $user_id = $user['id'];
    // there has some issue people can view other peoples item.
    if ($id) {
        $query = $app['db.builder']->select('*')->from('todos')->where('id =?')->andWhere('user_id=?')
            ->setParameter(0, $id)->setParameter(1, $user_id);
        $todo = $query->execute()->fetchAll();
        return $app['twig']->render('todo.html', [
            'todo' => $todo[0],
        ]);
    } else {
        $query = $app['db.builder']->select('*')->from('todos')->where('user_id =?')
            ->setParameter(0, $user_id);
        $todos = $query->execute()->fetchAll();
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

    $query = $app['db.builder']->insert('todos')->values(['user_id' => '?', 'description' => "?"])->setParameters([0 => $user_id, 1 => $description]);
    $query->execute();
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];
    $query = $app['db.builder']->delete('todos')->where('id =?')->andWhere('user_id=?')->setParameter(0, $id)->setParameter(1, $user_id);
    $query->execute();
    return $app->redirect('/todo');
});