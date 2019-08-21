<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//ORM
use Src\Model\Users;
use Src\Model\Todos;

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

    //if user's session is avialable
    if (null !== $user = $app['session']->get('user')) {
        return $app->redirect('/todo');
    }

    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        //password should not save in session
        $user = Users::where(['username' => $username, 'password' => $password])->get(['id', 'username'])->first();

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
        $todo = Todos::find($id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $todos = Todos::where(['user_id' => $user->id])->get();

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

    $description = $request->get('description');

    Todos::create([
        'user_id' => $user->id,
        'description' => $description
        ]);

    return $app->redirect('/todo');
});


$app->match('/todo/{id}/delete', function ($id) use ($app) {
    //this action should after login
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    //recode only can be removed by its owner
    $targetData = Todos::find($id);
    if($targetData->user_id != $user->id)
    {
        return $app->redirect('/todo');
    }

    Todos::destroy($id);

    return $app->redirect('/todo');
});