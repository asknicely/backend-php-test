<?php
namespace App\Test;

use Silex\WebTestCase;

class HomeTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../src/app.php';
        $app['session.test'] = true;
        return $app;
    }

    public function testGetHomePage()
    {
        $client = $this->createClient();
        $client->request('GET', '/todo/1/json');
        $response = $client->getResponse()->getContent();
    }
}