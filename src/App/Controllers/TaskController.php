<?php


namespace App\Controllers;

use \App\Entities\Task;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TaskController
 *
 * Controls actions performed on the Task model
 *
 * @package App\Controllers
 */
class TaskController extends Common
{
    private $_limit = 10;

    /**
     * Retrieves individual task from DB.
     * Return false if task is not found or belongs to a different user.
     *
     * @param $id
     * @return mixed
     */
    public function getTask($id)
    {
        $task = $this->_em->find('\App\Entities\Task', $id);

        if ($task && $task->user_id === (int)$this->_user['id']) {
            return $task;
        }

        return false;
    }

    /**
     * Get a collection of tasks for the current user.
     *
     * @param $page
     * @return mixed
     */
    public function getTasks($page)
    {
        $_offset = $this->_limit * ($page - 1);

        return $this->_em->getRepository('\App\Entities\Task')->findBy(['user_id' => (int)$this->_user['id']], null, $this->_limit, $_offset);
    }

    /**
     * Get total number of pages required for current user's tasks.
     *
     * @return float
     */
    public function getTotalPages()
    {
        $tasks = $this->_em->getRepository('\App\Entities\Task')->findBy(['user_id' => (int)$this->_user['id']]);

        return ceil(count($tasks) / $this->_limit);
    }

    /**
     * Create a new task for the current user.
     *
     * @param $description
     * @return bool
     */
    public function createTask($description)
    {
        $errors = $this->_app['validator']->validate($description, new Assert\NotBlank());

        if (count($errors) === 0) {
            $task = new Task();
            $task->setUserId((int)$this->_user['id']);
            $task->setDescription($description);
            $task->setCompleted(0);

            $this->_em->persist($task);
            $this->_em->flush();

            return $task->getId();
        }

        return false;
    }

    /**
     * Mark a specific task as complete.
     *
     * @param $id
     * @return bool
     */
    public function completeTask($id)
    {
        $task = $this->getTask($id);

        if ($task) {
            $task->setCompleted(1);

            $this->_em->persist($task);
            $this->_em->flush();

            return true;
        }

        return false;
    }

    /**
     * Delete a specific task.
     *
     * @param $id
     * @return bool
     */
    public function deleteTask($id)
    {
        $task = $this->getTask($id);

        if ($task) {
            $this->_em->remove($task);
            $this->_em->flush();

            return true;
        }

        return false;
    }
}