<?php

namespace App\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Todo
{
    /**
     * Todo task id key
     * @var int
     *
     */
    private $id;

    /**
     * User id that the todo belongs to.
     * @var int
     *
     */
    private $user_id;

    /**
     * Description.
     * @var string
     *
     */
    private $description;

    /**
     * Status flag for the todo.
     * @var boolean
     *
     */
    private $completed;

    /**
     * @param string $id
     *
     * @return object, $todo
     */
    public function getTodoById($app, $id)
    {
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);
        return $todo;
    }

    /**
     * @param string $userID
     *
     * @return Array, $todos
     */
    public function getTodosByUserID($app, $userID)
    {
        $sql = "SELECT * FROM todos WHERE user_id = '$userID'";
        $todos = $app['db']->fetchAll($sql);
        return $todos;
    }

    /**
     * @param string $userID
     * @param string $description
     *
     * @return null
     */
    public function createTodo($app, $userID, $description)
    {
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$userID', '$description')";
        $app['db']->executeUpdate($sql);
    }

    /**
     * @param string $todoID
     *
     * @return null
     */
    public function deleteTodo($app, $todoID)
    {
        $sql = "DELETE FROM todos WHERE id = '$todoID'";
        $app['db']->executeUpdate($sql);
    }

    /**
     * @param string $todoID
     *
     * @return null
     */
    public function completeTodo($app, $todoID)
    {
        $sql = "UPDATE todos SET completed = TRUE WHERE id='$todoID'";
        $app['db']->executeUpdate($sql);
    }

    /**
     * @param string $todoID
     *
     * @return null
     */
    public function uncompleteTodo($app, $todoID)
    {
        $sql = "UPDATE todos SET completed = FALSE WHERE id='$todoID'";
        $app['db']->executeUpdate($sql);
    }
}