<?php
namespace App\Controllers;

use App\Models\TodoModel;
use Symfony\Component\Validator\Constraints as Assert;

class TodoController
{

    private $app;
    private $model;
    function __construct($app)
    {
        $this->app = $app;
        $this->model = new TodoModel($app['db']);
    }

    public function get(int $id)
    {
        return $this->app['twig']->render('todo.html', [
            'todo' => $this->model->get($id),
        ]);
    }

    public function getByUserId(int $user_id) {
        return $this->app['twig']->render('todos.html', [
            'todos' => $this->model->getAllbyUser($user_id),
        ]);
    }

    public function add(int $user_id, string $description) {
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());
        if(count($errors) == 0) {
            $this->model->add($user_id, $description);
        }
        return $this->app->redirect('/todo');
    }

    public function delete(int $id) {
        $this->model->delete($id);
        return $this->app->redirect('/todo');
    }

    public function toggleComplete(int $id) {
        $this->model->toggleComplete($id);
        return $this->app->redirect('/todo');
    }

    // inject mock model for test
    public function setModel($model)
    {
        $this->model = $model;
    }
}
