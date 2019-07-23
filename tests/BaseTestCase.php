<?php

use Silex\Provider\TwigServiceProvider;
use Silex\WebTestCase;

class BaseTestCase extends WebTestCase
{
    protected $base_uri = 'http://dev-test.com/';

    protected $_app = null;

    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';


        $app->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../templates',
        ));

        $app['session.test'] = true;

        $app['session']->set('user', null);
        $app['debug'] = true;

        require __DIR__ . '/../src/controllers.php';

        $this->_app = $app;

        return $app;
    }
}