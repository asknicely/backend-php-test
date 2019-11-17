<?php
namespace Controllers\Api;

use Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TodoController
 *
 * @package Controllers\Api
 * @todo return proper error/success messages
 * @todo get rid of plain mysql queries
 * @todo create a repository
 */
class TodoController extends Controller
{
    /**
     * Get all todos created by the current user
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = $this->getUserId();

        $sql = "SELECT todos.*, users.username FROM todos 
            INNER JOIN users ON todos.user_id = users.id 
            WHERE user_id = {$userId}";
        $todos = $this->getConnection()->fetchAll($sql);

        return new JsonResponse($todos, Response::HTTP_OK);
    }

    /**
     * Get a specific todo with
     *
     * @param integer $id
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $userId = $this->getUserId();

        $sql = "SELECT todos.*, users.username FROM todos 
            INNER JOIN users ON todos.user_id = users.id 
            WHERE todos.id='$id'AND user_id = '$userId'";
        $todo = $this->getConnection()->fetchAssoc($sql);

        return new JsonResponse($todo, Response::HTTP_OK);
    }

    /**
     * Store a todo
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->getRequestContent($request);
        if (empty($data['description'])) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $description = $data['description'];
        $userId      = $this->getUserId();
        $sql         = "INSERT INTO todos (user_id, description) VALUES ({$userId}, '{$description}')";
        $this->getConnection()->executeUpdate($sql);

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * Delete a specific todo
     *
     * @param integer $id
     *
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $userId = $this->getUserId();

        $sql = "DELETE FROM todos WHERE id = {$id} AND user_id = '{$userId}'";
        $this->getConnection()->executeUpdate($sql);

        return new JsonResponse([], Response::HTTP_OK);
    }
}
