<?php
namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;
use Twig\Environment;

/**
 * TodoController
 *
 * @package Controllers
 * @todo get rid of plain mysql queries
 * @todo create a repository
 */
class TodoController extends Controller
{
    /**
     * Renders "Todo List" page
     *
     * @return string
     */
    public function index(): string
    {
        return $this->getTwig()->render('todos.html');
    }

    /**
     * Renders "Todo" page
     *
     * @param integer $id
     *
     * @return string
     */
    public function show(int $id): string
    {
        return $this->getTwig()->render('todo.html', [
            'id' => $id
        ]);
    }

    /**
     * Get a specific todo with
     *
     * @param integer $id
     *
     * @return JsonResponse
     */
    public function showJson(int $id): JsonResponse
    {
        $userId = $this->getUserId();

        $sql = "SELECT todos.*, users.username FROM todos 
            INNER JOIN users ON todos.user_id = users.id 
            WHERE todos.id={$id} AND user_id = {$userId}";
        $todo = $this->getConnection()->fetchAssoc($sql);

        return new JsonResponse($todo, Response::HTTP_OK);
    }
}
