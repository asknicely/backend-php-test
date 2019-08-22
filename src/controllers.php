<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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
    $username = $request->get('username');
    $password = $request->get('password');
    $password = hash('sha256', $password);

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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todo = Todos::find($id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $pageSize = 8;
        $count = Todos::where(['user_id' => $user->id])->get()->count();

        $maxpage = ceil($count / $pageSize);

        $page = $request->get('page', 1);
        $page = $page < 1 ? 1 : $page;
        $page = $page > $maxpage ? $maxpage : $page;

        $offset = $pageSize * ($page - 1);

        $todos = Todos::where(['user_id' => $user->id])->limit($pageSize)->offset($offset)->get();


        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'current' => $page,
            'max' => $maxpage,
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todo = Todos::find($id);

    if(!empty($todo))
    {
        $todotmp = $todo->toArray();
        unset($todotmp['status']);

        $todoInJson = json_encode($todotmp);
        return $app['twig']->render('todo.json.html', [
            'todo' => $todo,
            'json' => $todoInJson,
        ]);
    }
    else 
    {
        return $app->redirect('/todo');
    }
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    $description = trim($description);      //trim description first

    //validate description
    $error = $app['validator']->validate($description, new Assert\NotBlank());
    if(count($error) > 0)
    {
        $app['session']->getFlashBag()->add('error', 'Please add a description.');
    }
    else 
    {
        Todos::create([
            'user_id' => $user->id,
            'description' => $description
            ]);
        $app['session']->getFlashBag()->add('confirmation', 'New TODO added.');

    }

    return $app->redirect('/todo');
});


$app->match('/todo/{id}/delete', function ($id) use ($app) {
    //this action should after login
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $targetData = Todos::find($id);
    //there is no such a record
    if(empty($targetData))
    {
        return $app->redirect('/todo');
    }
    //recode only can be removed by its owner
    if($targetData->user_id != $user->id)
    {
        return $app->redirect('/todo');
    }

    Todos::destroy($id);

    $app['session']->getFlashBag()->add('confirmation', "TODO (id:{$id}) deleted!");

    return $app->redirect('/todo');
});

/**
 * Complete a toto record
 */
$app->match('/todo/{id}/complete', function ($id) use ($app) {
    //this action should after login
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    //add status in ENUM instead of BOOL. So that we can give the record other status, LILE highlight, not start ...
    $targetData = Todos::find($id);
    //there is no such a record
    if(empty($targetData))
    {
        return $app->redirect('/todo');
    }
    //recode only can be done by its owner
    if($targetData->user_id != $user->id)
    {
        return $app->redirect('/todo');
    }
    
    $targetData->status = 'completed';
    $targetData->save();
    $app['session']->getFlashBag()->add('confirmation', "TODO (id:{$id}) completed!");


    return $app->redirect('/todo');
});

/**
 * Undo complete action
 */
$app->match('/todo/{id}/undo', function ($id) use ($app) {
    //this action should after login
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    $targetData = Todos::find($id);
    //there is no such a record
    if(empty($targetData))
    {
        return $app->redirect('/todo');
    }
    //recode only can be done by its owner
    if($targetData->user_id != $user->id)
    {
        return $app->redirect('/todo');
    }

    $targetData->status = 'processing';
    $targetData->save();
    $app['session']->getFlashBag()->add('confirmation', "TODO (id:{$id}) changed to PROCESSING!");


    return $app->redirect('/todo');
});