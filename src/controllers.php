<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint as Assert;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {

    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

//dash board
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

//login in
$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    $em = $app["db.orm.em"];

    if ($username) {
        $user = $em->getRepository("Entity\User")->findOneBy(array("userName" => $username, "passWord" => $password));
        if ($user) {
            $app['session']->set('user', $user);
            return $app->redirect('/todos');
        }
    }

    return $app['twig']->render('login.html', array());
});

//log out
$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});

//todos list
$app->get("/todos", function (Request $request) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $page = $request->get('page');

    $errors = $app["validator"]->validate($page, new  \Symfony\Component\Validator\Constraints\GreaterThan(0));

    if (count($errors) > 0) {
        $app["monolog"]->debug(sprintf("Got errors %s when we validate the view todo as json format", (string)$errors));
        $app["session"]->getFlashBag()->add("warning", "Page is invalid");
        return $app->redirect('/todos');
    }

    $em = $app["db.orm.em"];

    //first we get the presist user from database
    $u = $em->getRepository("Entity\User")->find($user->getId());

    $todos = $em->createQueryBuilder()->select("t")->from("Entity\ToDo", "t")->where("t.author = ?1")->setParameter(1, $u);

    $adapter = new \Pagerfanta\Adapter\DoctrineORMAdapter($todos);

    try {
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($app["config"]["list"]["number"]);
        $pagerfanta->setCurrentPage($page);
    } catch (Exception $e) {
        $app["monolog"]->error(sprintf("Got an exception %s when we fetch the todos pagnation", $e->getMessage()));
        return $app->redirect('/todos');
    }

    return $app['twig']->render('todos.html', array(
        'todos' => $pagerfanta->getCurrentPageResults(),
        "pager" => $pagerfanta,
    ));


})->value("page", 1);

//todos detail
$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $errors = $app["validator"]->validate($id, new  \Symfony\Component\Validator\Constraints\GreaterThan(0));

    if (count($errors) > 0) {
        $app["monolog"]->debug(sprintf("Got errors %s when we validate the view todo as json format", (string)$errors));
        $app["session"]->getFlashBag()->add("warning", "Page is invalid");
        return $app->redirect('/todos');
    }

    $em = $app["db.orm.em"];

    //first we get the presist user from database
    $u = $em->getRepository("Entity\User")->find($user->getId());

    $todo = $em->getRepository("Entity\ToDo")->findOneBy(array("id" => $id, "author" => $u));

    if (!$todo) {
        $app["monolog"]->debug(sprintf("user %u's todo %t is not exists", $user->getId(), $id));
        return $app->redirect("/todos");
    }

    return $app['twig']->render('todo.html', array(
        'todo' => $todo,
    ));

})->value('id', null);


//show todos as json
$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $em = $app["db.orm.em"];

    $errors = $app["validator"]->validate($id, new  \Symfony\Component\Validator\Constraints\GreaterThan(0));

    if (count($errors) > 0) {
        $app["monolog"]->debug(sprintf("Got errors %s when we validate the view todo as json format", (string)$errors));
        return $app->redirect('/todos');
    }

    //first we get the presist user from database
    $u = $em->getRepository("Entity\User")->find($user->getId());

    $todo = $em->getRepository("Entity\ToDo")->findOneBy(array("id" => $id, "author" => $u));

    if (!$todo) {
        $app["monolog"]->debug(sprintf("user %u's todo %t is not exists", $user->getId(), $id));
        return $app->redirect("/todos");
    }

    return $app['twig']->render('todo_json.html', array(
        "id" => $id,
        'todo' => json_encode(array("id" => $todo->getId(), "user_id" => $todo->getAuthor()->getId(), "description" => $todo->getDescription())),
    ));
})->value('id', null);

//add todos
$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = $request->get('description');

    $errors = $app["validator"]->validate($description, new \Symfony\Component\Validator\Constraints\NotBlank());

    if (count($errors) > 0) {
        $app["monolog"]->debug(sprintf("Got errors %s when we validate the post add function", (string)$errors));
        foreach ($errors as $error) {
            $app["session"]->getFlashBag()->add("danger", $error->getMessage());
        }
        return $app->redirect('/todos');
    }

    $em = $app["db.orm.em"];
    $u = $em->getRepository("Entity\User")->find($user->getId());

    $t = new \Entity\ToDo();
    $t->setDescription($description);
    $t->setAuthor($u);
    $em->persist($t);
    $em->flush();
    if ($t->getId()) {
        $app["session"]->getFlashBag()->add("info", "Add todo success!");
    }
    return $app->redirect('/todos');
});

//delete todos
$app->post('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $em = $app["db.orm.em"];
    $u = $em->getRepository("Entity\User")->find($user->getId());
    $t = $em->getRepository("Entity\ToDo")->findOneBy(array("id" => $id, "author" => $u));
    $em->remove($t);
    $em->flush();
    if ($t->getId() == null) {
        $app["session"]->getFlashBag()->add("info", "Delete todo success!");
    }

    return $app->redirect('/todos');
});

// mark todos is done
$app->post('/todo/done/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $em = $app["db.orm.em"];
    $u = $em->getRepository("Entity\User")->find($user->getId());
    $t = $em->getRepository("Entity\ToDo")->findOneBy(array("id" => $id, "author" => $u));
    try {
        $t->setIsDone($t::ISDONE);
        $em->persist($t);
        $em->flush();
    } catch (InvalidArgumentException $e) {
        $app["monolog"]->warn(sprintf("Get an excetion %s when we set the is_done status", $e->getMessage()));
        return $app->redirect("/todos");
    }

    return $app->redirect('/todos');
});

