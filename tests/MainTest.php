<?php

require __DIR__ . '\BaseTestCase.php';

class MainTest extends BaseTestCase
{
    public function testLoginResponding()
    {
        $client = $this->createClient();
        $content = $client->request('GET', $this->base_uri . 'login');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(1, $content->filter('button:contains("login")'));
    }

    public function testTodoAuthCheck()
    {
        $client = $this->createClient();
        $content = $client->request('GET', $this->base_uri . 'todo');

        $response = $client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->asserttrue( $response->isRedirection());
    }

    public function testSingleTodoAuthCheck()
    {
        $client = $this->createClient();
        $content = $client->request('GET', $this->base_uri . 'todo/1');

        $response = $client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->asserttrue( $response->isRedirection());
    }

    public function testSingleTodoJsonAuthCheck()
    {
        $client = $this->createClient();
        $content = $client->request('GET', $this->base_uri . 'todo/1/json');

        $response = $client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->asserttrue( $response->isRedirection());
    }
}