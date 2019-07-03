<?php

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    try {
        $username = $request->get('username');
        $password = $request->get('password');

        if ($username) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $app['orm.em'];

            $qry = $em->createQuery("SELECT u FROM App\Entity\Users u WHERE u.username = :username AND u.password = :password");
            $qry->setParameters([
                'username' => $username,
                'password' => $password,
            ]);

            $user = $qry->getSingleResult();

            if (empty($user) === false){
                $app['session']->set('user', [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                ]);
                return $app->redirect('/todo');
            }
        }

        return $app['twig']->render('login.html', array());
    } catch (\Doctrine\ORM\NoResultException $e) {
        return $app['twig']->render('login.html', array());
    }
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    try {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];

        if ($id){
            $qry = $em->createQuery("SELECT t FROM App\Entity\Todos t WHERE t.id = :id AND t.user = :user");
            $qry->setParameters([
                'id' => $id,
                'user' => $user['id'],
            ]);

            $todo = $qry->getSingleResult();

            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        } else {
            $limit = (int) $request->get('limit', 5);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;

            $qry = $em->createQuery("SELECT count(t.id) FROM App\Entity\Todos t WHERE t.user = :user");
            $qry->setParameters([
                'user' => $user['id'],
            ]);

            $totalCount = $qry->getSingleScalarResult();

            $qry = $em->createQuery("SELECT t FROM App\Entity\Todos t WHERE t.user = :user");
            $qry->setFirstResult($offset)
                ->setMaxResults($limit)
                ->setParameters([
                    'user' => $user['id'],
                ]);

            $todos = $qry->getResult();

            return $app['twig']->render('todos.html', [
                'todos' => $todos,
                'onPage' => $page,
                'maxPages' => ceil($totalCount / $limit),
            ]);
        }
    } catch (\Doctrine\ORM\NoResultException $e) {
        return $app->redirect('/todo');
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    try {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $description = strip_tags($request->get('description'));

        if (empty($description)) {
            return $app->redirect('/todo');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];

        $user = $em->find('\App\Entity\Users', $user['id']);

        $todo = new \App\Entity\Todos();
        $todo->setDescription($description)
            ->setCompleted(false)
            ->setUser($user);

        $em->persist($todo);
        $em->flush();

        $app['session']->getFlashBag()->add('notifications', 'Success - Todo added');

        return $app->redirect('/todo');
    } catch (\Doctrine\ORM\NoResultException $e) {
        return $app->redirect('/todo');
    }
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    try {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];

        $qry = $em->createQuery("SELECT t FROM App\Entity\Todos t WHERE t.id = :id AND t.user = :user");
        $qry->setParameters([
            'id' => $id,
            'user' => $user['id'],
        ]);

        $todo = $qry->getSingleResult();
        $em->remove($todo);
        $em->flush();

        $app['session']->getFlashBag()->add('notifications', 'Success - Todo deleted');

        return $app->redirect('/todo');
    } catch (\Doctrine\ORM\NoResultException $e) {
        return $app->redirect('/todo');
    }
});

$app->match('/todo/completed/{id}', function ($id) use ($app) {
    try {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];

        $qry = $em->createQuery("SELECT t FROM App\Entity\Todos t WHERE t.id = :id AND t.user = :user");
        $qry->setParameters([
            'id' => $id,
            'user' => $user['id'],
        ]);

        $todo = $qry->getSingleResult();
        $todo->setCompleted(true);

        $em->persist($todo);
        $em->flush();

        return $app->redirect('/todo');
    } catch (\Doctrine\ORM\NoResultException $e) {
        return $app->redirect('/todo');
    }
});

$app->get('todo/{id}/json', function ($id) use ($app) {
        try {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }

            if (empty($id)) {
                return json_encode(new stdClass());
            }

            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $app['orm.em'];

            $qry = $em->createQuery("SELECT t FROM App\Entity\Todos t WHERE t.id = :id AND t.user = :user");
            $qry->setParameters([
                'id' => $id,
                'user' => $user['id'],
            ]);

            $todo = $qry->getSingleResult();
            return json_encode([
                'id' => $todo->getId(),
                'description' => $todo->getDescription(),
                'completed' => $todo->isCompleted()
            ]);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return json_encode(new stdClass());
        }
})
    ->value('id', null);