<?php

namespace BPT\Tests;

use Silex\Provider\TwigServiceProvider;
use Silex\WebTestCase;

class ControllerTest extends WebTestCase
{

    public function createApplication()
    {
        $app = require __DIR__ . '/../../../src/app.php';
        require __DIR__ . '/../../../src/controllers.php';
        $app->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../../../templates',
        ));
        $app['debug'] = true;
        $app['session.test'] = true;
        unset($app['exception_handler']);
        return $app;
    }

    public function testIndexPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("README")'));
    }

    public function testLoginPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Login")'));
    }
}
