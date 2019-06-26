<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

//Our add todo form
$app['form'] = $app['form.factory']->createBuilder(FormType::class)
    ->add('description', TextType::class, array(  //description field
        'constraints' => array(new Assert\NotBlank()))) //make sure its not blank
    ->add('submit', SubmitType::class, [
            'label' => 'Add',
    ])
    ->getForm();
    
   
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    //need to add translation in here because our form requires it, will be usefull in future when we add other languages
    $translator = new Translator('en');
    $translator->addLoader('xlf', new XliffFileLoader());
    $translator->addResource('xlf', './vendor/symfony/form/Resources/translations/validators.en.xlf', 'en', 'validators');
    $translator->addResource('xlf', './vendor/symfony/validator/Resources/translations/validators.en.xlf', 'en', 'validators');
    
    $twig->addExtension(new TranslationExtension($translator));
    
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


$app->get('/todo/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
        
    //clear flash bag of confirm
    $app['session']->getFlashBag()->get('confirm');
     
    if ($app['session']->getFlashBag()->has('error')) {
        $error = $app['session']->getFlashBag()->get('error')[0]['message'];
    } else {
        $error = '';
    }
    
    $page_size = 5;
    
    $offset = ($page - 1) * $page_size;
    
    $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}' LIMIT {$offset}, {$page_size}";
    $todos = $app['db']->fetchAll($sql);
    
    $sql = "SELECT COUNT(*) AS count FROM todos WHERE user_id = '${user['id']}'";
    $count = $app['db']->fetchAssoc($sql)['count'];
    $pages = ceil($count / $page_size);
    
    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'form' => $app['form']->createView(),
        'error' => $error,
        'pages' => $pages,
        'page' => $page
    ]);

})
->value('page', 1);


$app->get('/todo/view/{id}', function ($id) use ($app) {
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
        $app->abort(404, 'Todo not found');
    }
    
})
->value('id', null);


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    $user_id = $user['id'];
    
    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '$user_id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app->json($todo);
    } 
    
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];
    
    if (!$app['session']->getFlashBag()->has('confirm'))  {
    
            $app['form']->handleRequest($request);

            //Although we do have client side verification we still need to do server side verification. We send the user back to /todo with an error message
            if (!$app['form']->isValid()) {
                $error['message'] = 'Empty description, please check you have entered in a description';
                $app['session']->getFlashBag()->add('error', $error);
                return $app->redirect('/todo');
            }
            
            $confirm['action'] = 'add';
            $confirm['data'] = $app['form']->getData();
            $app['session']->getFlashBag()->add('confirm', $confirm);
   
           return $app['twig']->render('confirm.html', [
                'action' => 'add',
            ]);
     } else {
        
        $action = $app['session']->getFlashBag()->get('confirm')[0];
        if ($action['action'] == 'add')
        {
                $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '{$action['data']['description']}')";
                $app['db']->executeUpdate($sql);

                return $app->redirect('/todo');
        
        }
        else { 
            $app->abort(403, 'Invalid confirm');
        }
    }
});


$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed=1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

   if (!$app['session']->getFlashBag()->has('confirm'))  {

            $confirm['action'] = 'delete';
            $confirm['id'] = $id;
            $app['session']->getFlashBag()->add('confirm', $confirm);
   
           return $app['twig']->render('confirm.html', [
                'action' => 'delete',
            ]);
      } else {
        
        $action = $app['session']->getFlashBag()->get('confirm')[0];
        if ($action['action'] == 'delete')
        {
                
                $sql = "DELETE FROM todos WHERE id = '{$action['id']}'";
                $app['db']->executeUpdate($sql);

                return $app->redirect('/todo');
            } else {
                $app->abort(403, 'Did not confirm');
            }
       }
});
