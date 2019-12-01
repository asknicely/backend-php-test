<?php

require __DIR__ . '/../vendor/autoload.php';
use Silex\WebTestCase;

class controllersTest extends WebTestCase
{
    public function testGetHomepage()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');
        // Unable to work out why we are getting a 500 error response code at this stage.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
    public function createApplication()
    {
        $app = require __DIR__.'/../src/app.php';
        require __DIR__.'/../config/dev.php';
        require __DIR__.'/../src/controllers.php';
        return $this->app = $app;
    }
}