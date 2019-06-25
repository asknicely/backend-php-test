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


$app->get('/todo/{id}', function ($id) use ($app) {
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

        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);
        
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'form' => $app['form']->createView()
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
  
    //get our form data from the request
    $app['form']->handleRequest($request);

    //if our form data is valid then add the todo into the DB
    if ($app['form']->isValid()) {
        $data = $app['form']->getData();

        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '{$data['description']}')";
        $app['db']->executeUpdate($sql);

        return $app->redirect('/todo');
    }
    else {
        return $app->abort(403, 'Invalid description'); //No one should end up here as there is client side validation
    }
    
});


$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed=1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});
