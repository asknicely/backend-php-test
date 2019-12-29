<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Entity\Todo;

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

    $entityManager = $app['orm.em'];
    $repository = $entityManager->getRepository(\Entity\User::class);

    if ($username) {
        $user = $repository->findOneBy([
            'username' => $username,
            'password' => $password
        ]);

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

    $entityManager = $app['orm.em'];
    $repository = $entityManager->getRepository(Todo::class);

    if ($id){
        $todo = $repository->findOneBy(['id' => $id]);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $todos = $repository->findBy(['userId' => $user->getId()]);

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

    $user_id = $user->getId();
    $description = $request->get('description');

    $entityManager = $app['orm.em'];
    $todo = new Todo();
    $todo->setUserId($user_id);
    $todo->setDescription($description);

    $metadata = $app['validator.mapping.class_metadata_factory']->getMetadataFor('Entity\Todo');
    $metadata->addPropertyConstraint('description', new Assert\NotBlank());
    $errors = $app['validator']->validate($todo);

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $app['session']->getFlashBag()->add('description-empty', $error->getMessage());
        }
    } else {
        $entityManager->persist($todo);
        $entityManager->flush();
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $entityManager = $app['orm.em'];
    $repository = $entityManager->getRepository(Todo::class);
    $todo = $repository->findOneBy(['id' => $id]);

    $entityManager->remove($todo);
    $entityManager->flush();

    return $app->redirect('/todo');
});