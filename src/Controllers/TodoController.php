<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Models\Todo;


class TodoController extends Todo
{

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
    public function getAll()
    {
        $todos = Todo::getAllTodosFromCurrentUser($this->user['id'], $this->queryBuilder);

        return $this->app['twig']->render('todos.html', [
            'todos' => $todos,
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
}