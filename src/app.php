<?php

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app->register(new YamlConfigServiceProvider(__DIR__ . '/../config/config.yml'));

$capsule = new Capsule;
$capsule->addConnection([
    "driver" => "mysql",
    "host" => $app['config']['database']['host'],
    "database" => $app['config']['database']['dbname'],
    "username" => $app['config']['database']['user'],
    "password" => $app['config']['database']['password']
]);

$capsule->bootEloquent();

return $app;
