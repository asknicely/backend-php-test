<?php

namespace Controllers;

use Models\Todo;
use Kilte\Pagination\Pagination;

class TodoController extends Todo
{

    /**
     * Default values for pagination
     */
    private $perPage = 5;
    private $currentPage = 1;

    /**
     * Constructor
     */
    public function __construct($app)
    {

        $this->app = $app;

        $this->queryBuilder = $app['db']->createQueryBuilder();

        $this->user = $app['session']->get('user');
    }

    /**
     * Getting all todos from current user that is logged in
     */
    public function getAll($request)
    {
        // If there is a GET page, that's defining the current page
        if($request->get('page'))
            $this->currentPage = $request->get('page');

        // Counting all todos by current user for pagination
        $totalTodos = Todo::countByCurrentUser($this->user['id']);

        // Getting all todos from current user
        $todos = Todo::getAllTodosFromCurrentUser($this->user['id'], $this->perPage, $this->currentPage);

        // Pagination for todos
        $pagination = new Pagination($totalTodos, $this->currentPage, $this->perPage, 5);

        // Offset, limit, and pages going to view file for displaying pagination
        $offset = $pagination->offset();
        $limit = $pagination->limit();
        $pages = $pagination->build();

        return $this->app['twig']->render('todos.html', [
            'todos' => $todos,
            'offset' => $offset,
            'limit' => $limit,
            'pages' => $pages
        ]);
    }

    /**
     * Getting todo by ID
     */
    public function getOne($id)
    {
        $todo = Todo::getById($id);

        return $this->app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    }

    /**
     * Getting todo by ID and returning as JSON
     */
    public function getOneJSON($id)
    {
        $todo = Todo::getById($id);

        return json_encode($todo);
    }

    /**
     * Add new todo
     */
    public function addTodo($request)
    {
        if ($request->get('description')) {
            $data = array(
                'user_id' => $this->user['id'],
                'description' => $request->get('description')
            );

            $todo = Todo::insert($data);
            $this->app['session']->getFlashBag()->add('alert', 'Added new todo.');
        } else {
            $this->app['session']->getFlashBag()->add('alert', 'You have to add description.');
        }

        return $this->app->redirect('/todo');
    }

    /**
     * Delete todo
     */
    public function deleteTodo($id)
    {
        $todo = Todo::delete($id);
        $this->app['session']->getFlashBag()->add('alert', 'Deleted todo.');

        return $this->app->redirect('/todo');
    }

    /**
     * Complete or open again a todo
     */
    public function completeTodo($id)
    {
        Todo::complete($id);

        $this->app['session']->getFlashBag()->add('alert', 'Completed a todo.');

        return $this->app->redirect('/todo');
    }

}