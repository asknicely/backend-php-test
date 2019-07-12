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
    $password = md5($request->get('password'));

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
        $sql = "SELECT * FROM todos WHERE id = '$id' and user_id = '${user['id']}'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

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

    $user_id = $user['id'];
    $description = $request->get('description');

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    return $app->redirect('/todo');
});

/*
Changes:
 1) Todos listing 
 2) Delete Todo
 3) Change status to complete
	 Change todos status 
	 status 0: In progress
	 status 1: completed
 4) Add Todos 
Added by Hems
*/


$app->get('/todoslist', function () use ($app) {
  if (null === $user = $app['session']->get('user')) {
        return "login required";
    }
	
	
	$sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' ";
    $todos = $app['db']->fetchAll($sql);

	return $app['twig']->render('ajax_todos.html', [
		'todos' => $todos,
	]);
 })->value('id', null);
 
 $app->get('/todowithpagination/{pagenum}', function ($pagenum=1) use ($app) {
  if (null === $user = $app['session']->get('user')) {
        return "login required";
    }
	$totalpagedata=10;
	$startfrom = $pagenum;
	
	$startfrom = ($pagenum-1)*$totalpagedata;
	if($startfrom < 0) $startfrom = 0;
	$sql = "SELECT count(*) as totaldata FROM todos WHERE user_id = '${user['id']}' ";
    $todo = $app['db']->fetchAll($sql);
	
	$totaldata= $todo[0]["totaldata"];
	 
	$sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT  $startfrom , $totalpagedata";
    $todos = $app['db']->fetchAll($sql);
	
	$pages = ceil($totaldata/$totalpagedata);
	return $app['twig']->render('todoswithpagination.html', [
            'todos' => $todos, "totaldata" =>$totaldata, "pagenum" =>$pagenum,"pages"=>$pages
        ]);
		
	return ;
	
 });
 
 

$app->match('/todos/ajaxdelete/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
        return "login required";
    }
    $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '${user['id']}'";
    $app['db']->executeUpdate($sql);
    return "deleted successfully";
});

$app->match('/todos/completetodo/{id}', function ($id) use ($app) {
	if (null === $user = $app['session']->get('user')) {
        return "login required";
    }
    $sql = "UPDATE todos SET status=1 WHERE id = '$id' AND user_id = '${user['id']}'";
    $app['db']->executeUpdate($sql);

    return "updated successfully";
});


$app->post('/todos/ajaxadd', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return "login required";
    }
	$user_id = $user['id'];
    $description = $request->get('description');
	if(trim($description)== ""){
	     return "Please add description"; 
	}
    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $insertObj= $app['db']->executeUpdate($sql);

	$addedtodo['id'] =$app['db']->lastInsertId();
	$addedtodo['description'] =$description;
	$addedtodo['user_id'] =$user_id;
	return json_encode($addedtodo);
});


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
       $todo["error"]= "Unauthorized  Access";
	   return json_encode($todo);
    }
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '${user['id']}' ";
        $todo = $app['db']->fetchAssoc($sql);
		if(!$todo){
			$todo["error"]= "Todo not found";
		}
		return json_encode($todo);

})
->value('id', null);
$app->get('/todos/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        $todo["error"]= "Unauthorized  Access";
	   return json_encode($todo);
    }

      $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
      $todos = $app['db']->fetchAll($sql);
	  if(!$todos){
			$todos["error"]= "Todos not found found for Auth users";
		}
      return json_encode($todos);

})
->value('id', null);
