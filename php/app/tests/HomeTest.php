<?php

use Silex\WebTestCase;

class HomeTest extends WebTestCase
{
    public function createApplication()
    {
        $app = getAppInstance();
        $app['session.test'] = true;
        return $app;
    }

    public function testGetHomePage()
    {
        $client = $this->createClient();
        $client->request('GET', '/');
        $json = $client->getResponse()->getContent();
        $data = json_decode($json, true);
        var_dump($data);
    }
}