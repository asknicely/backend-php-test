<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

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

        // fetching data using ORM
        $em = $app['orm.em'];
        $todoObj = $em->find('\App\Entity\TodosEntity', $id);

        return $app['twig']->render('todo.html', [
            'todo' => $todoObj,
        ]);
    } else {
        $pageNum = $app['request']->get('page') ? : 1;
        $limit = 5;

        // we need count to set pagination on frontend
        $sql = "SELECT count(id) as count FROM todos WHERE user_id = ?";
        $todosCount = $app['db']->fetchAssoc($sql, array((int)$user['id']));
        $totalPages = ceil($todosCount['count']/$limit);

        $startLimit = $limit * ($pageNum - 1);
        $endLimit = $startLimit + $limit;

        $sql = "SELECT * FROM todos WHERE user_id = ? ORDER BY `todos`.`id` DESC LIMIT $startLimit, $endLimit";
        $todos = $app['db']->fetchAll($sql, array((int)$user['id']));

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'pageNum' => $pageNum,
            'count' => $todosCount['count'],
            'totalPages' => $totalPages
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

    // if description is blank just give an error message as we do not want user to add entry without desc
    if($description === ""){
        $app['session']->getFlashBag()->add('todoMessages', array("type"=>"danger", "message"=>'Please enter value for description'));
        return $app->redirect('/todo');
    }

    // inserting data using ORM
    $em = $app['orm.em'];
    $todoObj = new \App\Entity\TodosEntity();

    $todoObj->setUserId($user_id);
    $todoObj->setDescription($description);
    $todoObj->setIsComplete(0);

    $em->persist($todoObj);
    $em->flush();

    $app['session']->getFlashBag()->add('todoMessages', array("type"=>"success", "message"=>'Added successfully'));

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = ?";
    $app['db']->executeUpdate($sql, array($id));

    $app['session']->getFlashBag()->add('todoMessages', array("type"=>"success", "message"=>'Removed successfully'));
    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {
    // update value to 1 to mark todo as completed
    $sql = "UPDATE `todos` SET `is_complete` = ? WHERE `todos`.`id` = ?";
    $app['db']->executeUpdate($sql, array('1' , $id));

    //set success message when marked as completed
    $app['session']->getFlashBag()->add('todoMessages', array("type"=>"success", "message"=>'Marked as completed successfully'));
    return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {

    // join query to fetch username from users table
    $sql = "SELECT todos.id, user_id, users.username, description, is_complete FROM `todos` JOIN users ON user_id = users.id where todos.id = ?";
    $todoObj = $app['db']->fetchAssoc($sql, array($id));

    $response = new \Symfony\Component\HttpFoundation\JsonResponse();
    $response->setContent(json_encode($todoObj));

    return $response;
});