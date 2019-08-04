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
use ORM\Provider\DoctrineORMServiceProvider;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

$app = new Application();
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new FormServiceProvider());

$app->register(new YamlConfigServiceProvider(__DIR__.'/../config/config.yml'));
$app->register(new DoctrineServiceProvider, array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => $app['config']['database']['host'],
        'dbname'    => $app['config']['database']['dbname'],
        'user'      => $app['config']['database']['user'],
        'password'  => $app['config']['database']['password'],
        'charset'   => 'utf8',
    ),
));

$app->register(new DoctrineORMServiceProvider(), array(
    'db.orm.class_path'            => __DIR__.'/../vendor/doctrine/orm/lib',
    'db.orm.proxies_dir'           => __DIR__.'/../var/cache/doctrine/Proxy',
    'db.orm.proxies_namespace'     => 'DoctrineProxy',
    'db.orm.auto_generate_proxies' => true,
    'db.orm.entities'              => array(array(
        'type'      => 'annotation',
        'path'      => __DIR__.'/Entity',
        'namespace' => 'Entity',
    )),
));

$app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
    $manager = new Form\Extensions\Doctrine\Bridge\ManagerRegistry(null, array(), array('db.orm.em'), null, null, '\Doctrine\ORM\Proxy\Proxy');
    $manager->setContainer($app);
    $extensions[] = new Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($manager);
    return $extensions;
}));

// 404 - Page not found
$app->error(function (\Exception $e, $code) use ($app) {
    if (404 === $code) {
        return $app['twig']->render('404.html', array(
          'code' => '404',
        ));
    }
});

return $app;
