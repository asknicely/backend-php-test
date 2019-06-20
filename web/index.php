<?php

use Symfony\Component\Debug\Debug;

require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

// App
$app = require __DIR__.'/../src/app.php';
// Config
require __DIR__.'/../config/dev.php';
// Middlewares
require __DIR__.'/../src/auth.middleware.php';
// Controllers
require __DIR__.'/../src/controllers.php';

$app->run();
