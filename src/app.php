<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new YamlConfigServiceProvider(__DIR__ . '/../config/config.yml'));
$app->register(new DoctrineServiceProvider, array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => $app['config']['database']['host'],
        'dbname' => $app['config']['database']['dbname'],
        'user' => $app['config']['database']['user'],
        'password' => $app['config']['database']['password'],
        'charset' => 'utf8',
    ),
));
//$app->register(new Silex\Provider\SecurityServiceProvider(), array(
//    'security.firewalls' => array(
//        'foo' => array('pattern' => '^/foo'), // Example of an url available as anonymous user
//        'default' => array(
//            'pattern' => '^/todo/*$',
//            'anonymous' => true, // Needed as the login path is under the secured area
//            'form' => array('login_path' => '/login', 'check_path' => '/auth'),
//            'logout' => array('logout_path' => '/logout'), // url to call for logging out
//            'users' => $app->share(function () use ($app) {
//                // Specific class App\User\UserProvider is described below
//                return new \Auth\AuthProvider($app);
//            }),
//        ),
//    ),
//    'security.access_rules' => array(
//        // You can rename ROLE_USER as you wish
//        array('^/.+$', 'ROLE_USER'),
//        array('^/foo$', ''), // This url is available as anonymous user
//    )
//));


return $app;
