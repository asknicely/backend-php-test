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
use Silex\Provider\MonologServiceProvider;
use ORM\Provider\DoctrineORMServiceProvider;

$app = new Application();

$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new MonologServiceProvider(), array('monolog.logfile' => __DIR__ . "/../var/todos.log"));

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

//register the orm provider
//$app['autoloader']->registerNamespace('ORM', __DIR__ . '/vendor/silex-orm-extension/lib');
$app->register(new DoctrineORMServiceProvider(), array(
//    "db.orm.class_path" => __DIR__ . "/../vender/doctrine/orm/lib",
    "db.orm.proxies_dir" => __DIR__ . "/../var/cache/doctrine/Proxy",
    "db.orm.proxies_namespace" => "DoctrineProxy",
    "db.orm.auto_generate_proxies" => false,
    "db.orm.entities" => array(
        array(
            "type" => "annotation",
            "path" => __DIR__ . "/Entity",
            "namespace" => "Entity"
        )
    )

));

return $app;
