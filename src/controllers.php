<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Query;
use Entity\Todos;
use Form\TodosType;

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
        // Select a user where username and password are match through Entity Manager
        $em = $app['db.orm.em'];
        $entity = $em->getRepository('Entity\Users')->findOneBy(array('username' => $username, 'password' => hash('sha256', $password)));

        if ($entity){


            // Get ID and Username from entity
            $user = [
              'id' => $entity->getId(),
              'username' => $entity->getUsername()
            ];

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
        // // Select a todos by id through Entity Manager
        // $em = $app['db.orm.em'];
        // $entity= $em->getRepository('Entity\Todos')->find($id);
        //
        // // Return 404 if no record found or the record is not for current user
        // if (!$entity || $entity->getUser_id() != $user['id']) {
        //     $app->abort(404, 'No entity found for id '.$id);
        // }
        //
        // return $app['twig']->render('todo.html', [
        //     'todo' => $entity,
        // ]);

        // Redirecting user to todo list as the /todo/{id} page is not necessary
        return $app->redirect('/todo');
    } else {
        // Select all todos by logged in user id through Entity Manager
        $em = $app['db.orm.em'];
        $entity = $em->getRepository('Entity\Todos')
            ->findBy(
                array('user_id'=> $user['id'])
            );
        return $app['twig']->render('todos.html', [
            'todos' => $entity,
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

    // Validation - description cannot be blank
    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    if (count($errors) > 0) { // Return error message if validation is failed.
      $app['session']->getFlashBag()->add('error_message', 'Please enter description.');
    }
    else { // Insert new record into db if validation is passed.
      $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
      $app['db']->executeUpdate($sql);
      $app['session']->getFlashBag()->add('success_message', 'A new todo is added successfully.');

      // Insert new record through Entity Manager (unfortunately unable to get this working)
      // $todos = new Todos();
      // $todos->setUser_id($user_id);
      // $todos->setDescription($description);
      // $todos->setComplete(0);
      // $em = $app['db.orm.em'];
      // $em->persist($todos);
      // $em->flush();
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    // Delete a todo by ID through Entity Manager
    $em = $app['db.orm.em'];
    $entity = $em->getRepository('Entity\Todos')->find($id);

    // Return 404 if no record found or the record is not for current user
    if (!$entity || $entity->getUser_id() != $user['id']) {
        $app->abort(404, 'No entity found for id '.$id);
    }
    $em->remove($entity);
    $em->flush();

    // Set sucess flash message
    $app['session']->getFlashBag()->add('success_message', 'Todo #'.$id.' is deleted successfully.');

    return $app->redirect('/todo');
});

// This will be called by ajax when user clicked the checkbox in todo list
$app->post('/todo/updateComplete', function (Request $request) use ($app) {

    $id = $request->get('id');

    // Update the complete column in todos table by ID, if it sets to 0, update it to 1 and vice versa.
    $sql = "UPDATE todos SET complete = IF(complete = 0, 1, 0) WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    // Update record through Entity Manager (unfortunately unable to get this working)
    // $em = $app['db.orm.em'];
    // $entity = $em->getRepository('Entity\Todos')->find($id);
    // $newComplete = ($entity->getComplete() == 0 ? 1 : 0);
    // $entity->setComplete($newComplete);
    // $em->flush();

    return "success";
});


// This will be called by ajax when user clicked the delete button in todo list
$app->post('/todo/deleteTodo', function (Request $request) use ($app) {

    $id = $request->get('id');

    // Delete a todo by ID throught Entity Manager
    $em = $app['db.orm.em'];
    $entity = $em->getRepository('Entity\Todos')->find($id);

    $em->remove($entity);
    $em->flush();

    return "success";
});


// This will be called by ajax when user clicked the description field in todo list
$app->post('/todo/updateDescription', function (Request $request) use ($app) {

    $id = $request->get('id');
    $description = $request->get('description');

    // Update the description column in todos table by ID.
    $sql = "UPDATE todos SET description = '$description' WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    // Update record through Entity Manager (unfortunately unable to get this working)
    // $em = $app['db.orm.em'];
    // $entity = $em->getRepository('Entity\Todos')->find($id);
    // $entity->setDescription($description);
    // $em->flush();

    return "success";
});

// Return a todo in JSON format
$app->match('/todo/{id}/json', function ($id) use ($app) {

    // Select a todo by ID through Entity Manager
    $em = $app['db.orm.em'];
    $entity = $em->getRepository('Entity\Todos')->find($id);

    if ($entity){
        // Get data from entity
        $todo = [
          'id' => $entity->getId(),
          'user_id' => $entity->getUser_id(),
          'description' => $entity->getDescription(),
          'complete' => $entity->getComplete()
        ];

        // Return result in json format
        return json_encode($todo);
    }

    // return no content if no result found
    return json_encode(['error' => 'no content']);
});
