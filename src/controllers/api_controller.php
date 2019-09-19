<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/api/todos', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->json([
            'status' => 'Not Found'
        ], 404);
    }

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
    $todos = $app['db']->fetchAll($sql);

    return $app->json($todos);
});