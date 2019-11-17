<?php
namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;
use Silex\Application;
use Twig\Environment;

/**
 * Controller
 *
 * @package Controllers
 */
abstract class Controller
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return Environment
     */
    protected function getTwig(): Environment
    {
        return $this->app['twig'];
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->app['db'];
    }

    /**
     * @return Session
     */
    protected function getSession(): Session
    {
        return $this->app['session'];
    }

    /**
     * @return integer|null
     */
    protected function getUserId(): ?int
    {
        $user = $this->getSession()->get('user');
        return $user['id'] ?? null;
    }
}
