<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/api/todos', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->json([
            'status' => 'Unauthorized'
        ], 401);
    }

    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
    $todos = $app['db']->fetchAll($sql);

    return $app->json($todos);
});

$app->post('/api/todo/changeCompleteStatus/{id}', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->json([
            'status' => 'Unauthorized'
        ], 401);
    }

    $user_id = $user['id'];
    $id = $request->get('id');

    $data = json_decode($request->getContent(), true);

    $is_completed = (int) $data['is_completed'];

    $sql = "UPDATE todos SET is_completed = {$is_completed} WHERE id = '{$id}' AND user_id = '{$user_id}'";
    $app['db']->executeUpdate($sql);

    return $app->json([
        'status' => 'ok'
    ]);
});

$app->post('/api/todo/delete/{id}', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->json([
            'status' => 'Unauthorized'
        ], 401);
    }

    $user_id = $user['id'];
    $id = $request->get('id');

    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '$user_id'";
    $app['db']->executeUpdate($sql);

    return $app->json([
        'status' => 'ok'
    ]);
});