<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint as Assert;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {

    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    //Actully I don't suggest to do this, it's a waste of the server bandwidth,
    //if this is dynamic we can do this via database otherwise via the CDN
    $filePath = dirname(__FILE__) . "/../README.md";

    if (!file_exists($filePath)) {
        $app["monolog"]->debug(sprintf("%s is not exists.", $filePath));
        $fileContent = "Oops, we can't find the file";
    } else {
        $fileContent = file_get_contents($filePath);
    }

    return $app['twig']->render('index.html', array(
        'readme' => $fileContent,
    ));
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    $em = $app["db.orm.em"];

    if ($username) {
        $user = $em->getRepository("Entity\User")->findOneBy(array("userName" => $username, "passWord" => $password));
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


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $em = $app["db.orm.em"];

    //first we get the presist user from database
    $u = $em->getRepository("Entity\User")->find($user->getId());

    if ($id) {
        $todo = $em->getRepository("Entity\ToDo")->findOneBy(array("id" => $id, "author" => $u));

        if (!$todo) {
            $app["monolog"]->debug(sprintf("user %u's todo %t is not exists", $user->getId(), $id));
            return $app->redirect("/todo");
        }
        return $app['twig']->render('todo.html', array(
            'todo' => $todo,
        ));
    } else {
        $todos = $u->getTodos()->toArray();
        return $app['twig']->render('todos.html', array(
            'todos' => $todos,
        ));
    }
})->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = $request->get('description');

    $errors = $app["validator"]->validate($description, new \Symfony\Component\Validator\Constraints\NotBlank());

    if (count($errors) > 0) {
        $app["monolog"]->debug(sprintf("Got errors %s when we validate the post add function", (string)$errors));
        return $app->redirect('/todo');
    }

    $em = $app["db.orm.em"];
    $u = $em->getRepository("Entity\User")->find($user->getId());

    $t = new \Entity\ToDo();
    $t->setDescription($description);
    $t->setAuthor($u);
    $em->persist($t);
    $em->flush();

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $em = $app["db.orm.em"];
    $u = $em->getRepository("Entity\User")->find($user->getId());
    $t = $em->getRepository("Entity\ToDo")->findOneBy(array("id" => $id, "author" => $u));
    $em->remove($t);
    $em->flush();

    return $app->redirect('/todo');
});