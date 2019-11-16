<?php
namespace Controllers\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;

/**
 * Controller
 *
 * @package Controllers\Api
 */
abstract class Controller
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Connection $db
     * @param Session $session
     */
    public function __construct(Connection $db, Session $session)
    {
        $this->db      = $db;
        $this->session = $session;
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->db;
    }

    /**
     * @return integer|null
     */
    protected function getUserId(): ?int
    {
        $user = $this->session->get('user');
        return $user['id'] ?? null;
    }

    /**
     * @param Request $request
     *
     * @return boolean
     */
    protected function isJsonRequest(Request $request): bool
    {
        return 0 === strpos($request->headers->get('Content-Type'), 'application/json');
    }

    /**
     * Returns parsed request content
     *
     * @param Request $request
     * @return array
     */
    protected function getRequestContent(Request $request): array
    {
        return json_decode($request->getContent(), true);
    }
}
