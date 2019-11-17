<?php
namespace Controllers\Api;

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
 * @todo validate request data in custom Request objects
 * @todo move all the login from the controller
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

        // @todo all queries should be done in a repository
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

        // @todo all queries should be done in a repository
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
        // @todo validation should be done in a custom request class
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->getRequestContent($request);
        if (empty($data['description'])) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        // @todo all queries should be done in a repository
        $description = htmlspecialchars($data['description'], ENT_QUOTES);
        $userId      = $this->getUserId();
        $sql         = "INSERT INTO todos (user_id, description) VALUES ({$userId}, '{$description}')";
        $result      = $this->getConnection()->executeUpdate($sql);
        $status      = $result ? Response::HTTP_CREATED : Response::HTTP_INTERNAL_SERVER_ERROR;

        return new JsonResponse([], $status);
    }

    /**
     * Update
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        // @todo validation should be done in a custom request class
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->getRequestContent($request);
        if (!isset($data['completed'])) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $completed = (int) $data['completed'];
        $userId    = $this->getUserId();

        // @todo all queries should be done in a repository
        $sql    = "UPDATE todos SET completed={$completed} WHERE id={$id} AND user_id={$userId}";
        $result =$this->getConnection()->executeUpdate($sql);
        $status = $result ? Response::HTTP_NO_CONTENT : Response::HTTP_INTERNAL_SERVER_ERROR;

        return new JsonResponse([], $status);
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

        // @todo all queries should be done in a repository
        $sql    = "DELETE FROM todos WHERE id = {$id} AND user_id = '{$userId}'";
        $result = $this->getConnection()->executeUpdate($sql);
        $status = $result ? Response::HTTP_NO_CONTENT : Response::HTTP_INTERNAL_SERVER_ERROR;

        return new JsonResponse([], $status);
    }
}
