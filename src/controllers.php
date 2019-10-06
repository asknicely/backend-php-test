<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));
    $twig->addGlobal('pages', []);
    $twig->addGlobal('pageScripts', '');

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


/**
 * Login action
 */
$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


/**
 * Logout action
 */
$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


/**
 * Todo list, detail, and json display
 */
$app->get('/todo/{id}/{json_flag}', function ($id, $json_flag) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

	// if a non empty id has been supplied then load and display the todo
    if ($id){
        $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
        $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);
        if (false == $todo) {
            return $app->redirect('/todos');
        }

		// if the 'json_flag' was set, then return the page content as an encoded json string
		if ($json_flag) {
			return json_encode($todo);
		}
		else {
			return $app['twig']->render('todo.html', [
				'todo' => $todo,
			]);
		}
    }
    else {
        return $app->redirect('/todos');
    }
})
->value('id', null)->value('json_flag', null);


/**
 * Todos list with pagination
 */
$app->get('/todos/{number}/{get_content_flag}', function ($number, $get_content_flag) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // set a records per page limit of 5 just to demonstrate the pagination task
    $resultsPerPage = 5;

    // find how many todos there are, to help with pagination
    $sql = "SELECT COUNT(id) AS cnt FROM todos WHERE user_id = ?";
    $row = $app['db']->fetchAssoc($sql, [$user['id']]);

    $pageCount = ceil($row['cnt'] / $resultsPerPage);
    for ($i = 0; $i < $pageCount; $i++) {
        if ($i == $number)
            $pages[] = 'current';
        else if ($i == 0)
            $pages[] = 'first';
        else if ($i == $pageCount)
            $pages[] = 'last';
        else if ($i < $number)
            $pages[] = 'previous';
        else if ($i > $number)
            $pages[] = 'next';
    }
    $currentPage = $number;

    if (false === $get_content_flag) {
        // render the todos page
        return $app['twig']->render('todos.html', ['currentPage' => $currentPage]);
    }
    else if ('pagination' == $get_content_flag) {
        $content = $app['twig']->render('todo-pagination.html', [
            'pages' => $pages,
        ]);
        return json_encode(['html' => $content, 'status' => 'success']);
    }
    else if ('todos' == $get_content_flag) {
        //
        $sql = "SELECT * FROM todos WHERE user_id = ? LIMIT " . ($currentPage * $resultsPerPage) . ", " . $resultsPerPage;
        $todos = $app['db']->fetchAll($sql, [$user['id']]);

        $content = [];
        foreach ($todos as $todo) {
            $content[] = $app['twig']->render('todo-row.html', [
                'pages' => $pages,
                'todo' => $todo,
            ]);
        }
        return json_encode(['html' => $content, 'status' => 'success']);
    }
})
->value('number', 0)->value('get_content_flag', false);



/**
 *  Toggle the isComplete flag of a todo
 */
$app->post('/todo/{id}/toggle_complete', function ($id) use ($app) {
    $response = ['status' => 'error', 'is_complete' => 0];
    if ((null === $user = $app['session']->get('user')) || empty($id)) {
        return json_encode($response);
    }

    // pull the todo
    $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
    $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);
    if ($todo) {

        // toggle the current 'is_complete' flag
        $is_complete = ($todo['is_complete'] == 1 ? 0 : 1);

        // update the todo with the new flag
        $sql = "UPDATE todos SET is_complete = ? WHERE id = ?";
        $app['db']->executeUpdate($sql, [$is_complete, $id]);

        $response['status'] = 'success';
        $response['is_complete'] = $is_complete;
    }

    return json_encode($response);
})
->value('id', null);


/**
 * Todo addition action
 */
$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
	
	if (empty(trim($description))) {
        $app['request']->getSession()->getFlashBag()->add('error', "<b>Error</b>: Don't forget to add a description");
	}
	else {
		$sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
		$app['db']->executeUpdate($sql, [$user_id, $description]);

        $app['request']->getSession()->getFlashBag()->add('success', "<b>Success</b>: Your new Todo was saved successfully");
	}

    return $app->redirect('/todo');
});


/**
 * Todo deletion action
 */
$app->post('/todo/delete/{id}', function ($id) use ($app) {

    // test if the user is logged in
    if (null === $user = $app['session']->get('user')) {
        return json_encode(['status' => 'error']);
    }

    // check if the current user owns this todo
    $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
    $todo = $app['db']->fetchAssoc($sql, [$id, $user['id']]);
    if (!$todo) {
        // either the todo doesn't exist, or the current user doesn't own it, either way, a general answer can normally stop people from phishing
        // for active ids etc.
        return json_encode(['status' => 'error']);
    }

    // all good, now lets delete.
    $sql = "DELETE FROM todos WHERE id = ?";
    $app['db']->executeUpdate($sql, [$id]);

    return json_encode(['status' => 'success', 'id' => $id]);
});