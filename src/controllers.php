<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;

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


// GET [todo] list or view
$app->get('/todo', function (Request $request) use ($app) {
    // If it is not an AJAX request, we just render the view
    if (!$request->isXmlHttpRequest()) {
        return $app['twig']->render('todos.html');
    }
    
    $user = $app['session']->get('user');

    // Count total
    $sql = "SELECT COUNT(*) as total FROM todos WHERE user_id = '${user['id']}'";
    $total = $app['db']->fetchColumn($sql);

    // Query params
    $pageSize = $request->query->get('pageSize');
    $pageNumber = $request->query->get('pageNumber');

    // Pagination
    $start = ($pageNumber - 1 ) * $pageSize;
    
    // Fetch [todo]
    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT ${start}, ${pageSize}";
    $todos = $app['db']->fetchAll($sql);

    $response = array(
        'data' => $todos,
        'total' => $total
    );

    // Http Response
    return new JsonResponse($response, Response::HTTP_OK);
});

// GET [todo]
$app->get('/todo/{id}.{format}', function ($id, $format) use ($app) {
    $user = $app['session']->get('user');

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    return $app['twig']->render('todo.html', [
        'todo' => $todo,
        'format' => $format
    ]);
})
->assert('id', '\d+')
->assert('format', 'json')
->value('format', null);


// POST [todo]
$app->post('/todo', function (Request $request) use ($app) {
    // Payload validation rules
    $constraint = new Assert\Collection(array(
        'description' => new Assert\NotBlank()
    ));
    $payload = $request->request->all();
    // Validating the payload.
    $errors = $app['validator']->validate($payload, $constraint);

    // If we have errors, we fill the session flash bag and return to [todo] list
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $app['session']->getFlashBag()->add('error', $error->getPropertyPath().' '.$error->getMessage());
        }
        return $app->redirect('/todo');
    }
    
    $user = $app['session']->get('user');

    $user_id = $user['id'];
    $description = $request->get('description');

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});

// PUT [todo]
$app->put('/todo/{id}', function (Request $request, $id) use ($app) {
    // Payload validation rules
    $constraint = new Assert\Collection(array(
        'completed' => array(
            new Assert\NotBlank(),
            new Assert\Regex('/0|1/')
        )
    ));
    $payload = $request->request->all();
    // Validating the payload.
    $errors = $app['validator']->validate($payload, $constraint);

    // If we have errors, we response with http bad request
    if (count($errors) > 0) {
        return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
    }

    // Next status
    $nextStatus = $payload['completed'];

    // Update [todo]
    $sql = "UPDATE todos SET completed = '$nextStatus' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('success', 'TODO has been updated successfully!');

    // Http Response
    return new Response(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
})
->assert('id', '\d+');


// DELETE [todo]
$app->delete('/todo/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    // Flash message
    $app['session']->getFlashBag()->add('success', 'TODO deleted successfully!');
    
    // Http Response
    return new Response(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
})
->assert('id', '\d+');