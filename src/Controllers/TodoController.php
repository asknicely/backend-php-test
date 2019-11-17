<?php
namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TodoController
 *
 * @package Controllers
 */
class TodoController extends Controller
{
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
            WHERE todos.id='$id'AND user_id = '$userId'";
        $todo = $this->getConnection()->fetchAssoc($sql);

        return new JsonResponse($todo, Response::HTTP_OK);
    }
}
