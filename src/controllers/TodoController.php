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

    public function getJson(int $id)
    {
        return $this->app->json($this->model->get($id));
    }

    public function getByUserId(int $user_id)
    {
        return $this->app['twig']->render('todos.html', [
            'todos' => $this->model->getAllbyUser($user_id),
        ]);
    }

    public function add(int $user_id, string $description)
    {
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());
        if (count($errors) == 0) {
            $this->model->add($user_id, $description);
            $this->app['session']->getFlashBag()->add('notice', 'add success');
        }
        return $this->app->redirect('/todo');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        $this->app['session']->getFlashBag()->add('notice', 'delete success');
        return $this->app->redirect('/todo');
    }

    public function toggleComplete(int $id)
    {
        $this->model->toggleComplete($id);
        return $this->app->redirect('/todo');
    }

    public function getByUserIdWithPagination(int $userId, int $pageNum = 1, int $pageSize = 5)
    {
        /*
          [
              todos => [],
              pageNum => 0,
              pageSize => 5,
              totalPage => 10,
          ]
        */
        $data = $this->model->getByUserIdWithPagination($userId, $pageNum - 1, $pageSize);
        return $this->app['twig']->render('todos.html', $data);
    }

    // inject mock model for test
    public function setModel($model)
    {
        $this->model = $model;
    }
}
