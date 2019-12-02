<?php

include_once 'validations/ValidationHandler.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use AsknicelyTest\ValidationHandler\ValidationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

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


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT count(*) as n FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        $itemsPerPage = 3;
        $lastPage = 1;
        $currentPage = 1;
        if($todos[0]['n'] > $itemsPerPage){
            $lastPage = ceil($todos[0]['n'] / $itemsPerPage);
            $currentPage = (is_numeric($request->query->get('page')))
                            ?$request->query->get('page')
                            :'1';
        };
        if($request->query->get('page') > $lastPage ){
            return $app->redirect('/todo?page=1');
        }
        $from = ($currentPage -1) * $itemsPerPage;
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' ORDER BY `id` desc LIMIT $from, $itemsPerPage";

        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'next' => ($currentPage < $lastPage) ? $currentPage + 1 : '0',
            'prev' => ($currentPage > 1) ? $currentPage - 1:'0'
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

    //Validate on front end too
    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) > 0) {
        $validationHandler = new ValidationHandler($app);
        $validationHandler->fail("Description can not be empty!", 400, ValidationHandler::FLASH);
    } else {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);
        $app['session']->getFlashBag()->add('Flash', 'Task created successfully!');// :P
    }
    


    return $app->redirect('/todo');
});


$app->post('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return new JsonResponse(["MESSAGE" => 'Session not found', "CODE" => Response::HTTP_UNAUTHORIZED], Response::HTTP_UNAUTHORIZED);
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);
    if($todo == false ){
        return new JsonResponse(["MESSAGE" => 'Task not found', "CODE" => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
    }

        

    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
        $data = json_decode($request->getContent(), true);
        if(is_numeric($data["completed"])  
            && (
                $data["completed"] === "0" 
                || 
                $data["completed"] === "1"
                )
        ){

            $sql = "UPDATE todos SET completed = '" . $data['completed'] . "' WHERE id = '" . $id  . "'";
            $todo = $app['db']->executeUpdate($sql);

            return new JsonResponse([
                    "MESSAGE" => 'Status updated', 
                    "CODE" => '200'], 
                    Response::HTTP_OK);
        }
    }
    return new JsonResponse(["MESSAGE" => 'Task not found', "CODE" => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
});

$app->get('/todo/{id}/json', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return new JsonResponse([
            "MESSAGE" => 'Session not found', 
            "CODE" => Response::HTTP_UNAUTHORIZED,
            "LOGIN" => 'http://'.$request->headers->get('host').'/login'
        ], Response::HTTP_UNAUTHORIZED);
    }

    if ($id){
        
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);
        if($todo == false ){
            return new JsonResponse(["MESSAGE" => 'Task not found', "CODE" => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            "MESSAGE" => 'Task found', 
            "CODE" => '200',
            "DATA" => $todo
        ], Response::HTTP_OK);
    } 
    return new JsonResponse(["MESSAGE" => 'Task not found', "CODE" => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
})
->value('id', null);

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('Flash', 'Task deleted successfully!');
    return $app->redirect('/todo');
});