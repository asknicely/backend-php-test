<?php

use Symfony\Component\Debug\Debug;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$capsule = new Capsule;
$capsule->addConnection(require __DIR__ . '/../config/database.php');
$capsule->bootEloquent();

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/dev.php';
require __DIR__.'/../src/controllers.php';
//include ORM
require __DIR__.'/../src/Model/Users.php';
require __DIR__.'/../src/Model/Todos.php';
$app->run();
