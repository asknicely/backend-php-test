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
            return $app->redirect('/todos');
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
    }
})
->value('id', null);

$app->get('/todos/{page}',
        function ($page) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            $entityManager = $app['orm.em'];
            $repository = $entityManager->getRepository(Todo::class);
            $todos = $repository->findBy(['userId' => $user->getId(), 'completed' => null]);
            $todoItems = count($todos);

            // Set items per page
            $itemsPerPage = 3;
            $offset = ($page - 1) * $itemsPerPage;

            $pagination = $app['pagination']($todoItems, $page, $itemsPerPage, 2 );
            $pages      = $pagination->build();

            $todos = $repository->findBy(['userId' => $user->getId(), 'completed' => null],['id' => 'DESC'], $itemsPerPage, $offset);

            return $app['twig']->render('todos.html', [
                'todos' => $todos,
                'pages' => $pages,
                'current' => $pagination->currentPage()
            ]);
        }
    )
    ->value('page', 1)
    ->convert(
        'page',
        function ($page) {
            return (int) $page;
        }
    );


$app->post('/todos/add', function (Request $request) use ($app) {
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
        $app['session']->getFlashBag()->add('description', 'Your Todo has been added.');
        $entityManager->persist($todo);
        $entityManager->flush();
    }

    return $app->redirect('/todos');
});


$app->match('/todos/delete/{id}', function ($id) use ($app) {

    $entityManager = $app['orm.em'];
    $repository = $entityManager->getRepository(Todo::class);
    $todo = $repository->findOneBy(['id' => $id]);

    $app['session']->getFlashBag()->add('description', 'Your Todo has been deleted.');
    $entityManager->persist($todo);
    $entityManager->flush();

    $entityManager->remove($todo);
    $entityManager->flush();

    return $app->redirect('/todos');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $entityManager = $app['orm.em'];
    $repository = $entityManager->getRepository(Todo::class);

    $todo = $repository->findOneBy(['id' => $id]);

    if (!$todo) {
        $error = array('message' => 'The todo was not found.');

        return $app->json($error, 404);
    }

    $json = [
        'id' => $todo->getId(),
        'user_id' => $todo->getUserId(),
        'description' => $todo->getDescription()
    ];

    $response = new \Symfony\Component\HttpFoundation\JsonResponse();
    $response->setEncodingOptions(JSON_NUMERIC_CHECK);
    $response->setData($json);
    return $response;
});

$app->match('/todo/mark/{id}', function ($id) use ($app) {

    $entityManager = $app['orm.em'];
    $repository = $entityManager->getRepository(Todo::class);
    $todo = $repository->findOneBy(['id' => $id]);

    $app['session']->getFlashBag()->add('description', 'Your Todo has been marked as completed.');
    $todo->setCompleted(1);
    $entityManager->flush();

    return $app->redirect('/todos');
});