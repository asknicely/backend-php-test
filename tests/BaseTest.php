<?php

namespace Tests;

use Silex\WebTestCase;

class BaseTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';
        require __DIR__ . '/../config/dev.php';
        require __DIR__ . '/../src/controllers.php';
        
        $app["debug"] = true;
        unset($app["exception_handler"]);
        $app["session.test"] = true;
        return $app;
    }
}