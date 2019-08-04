<?php
namespace Functional;

use Silex\WebTestCase;
use Silex\Application;

class ApplicationTest extends WebTestCase
{
    /** @test */
    public function createApplication()
    {
        // Silex
        $app = new Application();
        require __DIR__.'/../../config/test.php';
        require __DIR__.'/../../src/app.php';
        $app['session.test'] = true;
        // Controllers
        require __DIR__ . '/../../src/controllers.php';
        return $this->app = $app;
    }

    public function test404()
    {
        $client = $this->createClient();

        $client->request('GET', '/display-a-404-page');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAddTodo()
    {
        $client = $this->createClient();

        $client->request('GET', '/todo/add');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
