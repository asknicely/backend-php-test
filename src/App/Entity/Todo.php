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
     * @param object $app
     * @param string $id
     *
     * @return object, $todo
     */
    public function getTodoById($app, $todoID)
    {
        $allowed = self::todoBelongsToCurrentlyLoggedInUser($app, $todoID);
        if($allowed){
            $sql = "SELECT * FROM todos WHERE id = '$todoID'";
            $todo = $app['db']->fetchAssoc($sql);
            return $todo;
        } else {
            return false;
        }
    }

    /**
     * @param object $app
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
        $allowed = self::todoBelongsToCurrentlyLoggedInUser($app, $todoID);
        if($allowed){
            $sql = "DELETE FROM todos WHERE id = '$todoID'";
            $app['db']->executeUpdate($sql);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $todoID
     *
     * @return null
     */
    public function completeTodo($app, $todoID)
    {
        $allowed = self::todoBelongsToCurrentlyLoggedInUser($app, $todoID);
        if($allowed){
            $sql = "UPDATE todos SET completed = TRUE WHERE id='$todoID'";
            $app['db']->executeUpdate($sql);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $todoID
     *
     * @return null
     */
    public function uncompleteTodo($app, $todoID)
    {
        $allowed = self::todoBelongsToCurrentlyLoggedInUser($app, $todoID);
        if($allowed){
            $sql = "UPDATE todos SET completed = FALSE WHERE id='$todoID'";
            $app['db']->executeUpdate($sql);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $todoID, $userID
     *
     * @return boolean
     */
    private function todoBelongsToCurrentlyLoggedInUser($app, $todoID){
        $sql = "SELECT * FROM todos WHERE id = '$todoID'";
        $todo = $app['db']->fetchAssoc($sql);
        $user = $app['session']->get('user');
        if ($todo['user_id'] == $user['id']){
            return true;
        } else {
            return false;
        }
    }
}