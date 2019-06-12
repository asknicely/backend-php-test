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

    // inject mock model for test
    public function setModel($model)
    {
        $this->model = $model;
    }

    private function getLoginUser()
    {
        return $this->app['session']->get('user');
    }

    private function redirect(string $url = '/login')
    {
        return $this->app->redirect($url);
    }

    public function get(int $id)
    {
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }

        return $this->app['twig']->render('todo.html', [
            'todo' => $this->model->get($usr['id'], $id),
        ]);
    }

    public function getJson(int $id)
    {
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }

        return $this->app->json($this->model->get($usr['id'], $id));
    }

    public function getByUserId()
    {
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }
        return $this->app['twig']->render('todos.html', [
            'todos' => $this->model->getAllbyUser($usr['id']),
        ]);
    }

    public function add(string $description)
    {
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }

        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());
        if (count($errors) == 0) {
            $this->model->add($usr['id'], $description);
            $this->app['session']->getFlashBag()->add('notice', 'add success');
        }
        return $this->app->redirect('/todo');
    }

    public function delete(int $id)
    {
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }

        $this->model->delete($usr['id'], $id);
        $this->app['session']->getFlashBag()->add('notice', 'delete success');
        return $this->redirect('/todo');
    }

    public function toggleComplete(int $id)
    {
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }

        $this->model->toggleComplete($usr['id'], $id);
        return $this->app->redirect('/todo');
    }

    public function getByUserIdWithPagination(int $pageNum = 1, int $pageSize = 5)
    {
        /*
          [
              todos => [],
              pageNum => 0,
              pageSize => 5,
              totalPage => 10,
          ]
        */
        $usr = $this->getLoginUser();
        if ($usr === null) {
            return $this->redirect();
        }

        $data = $this->model->getByUserIdWithPagination($usr['id'], $pageNum - 1, $pageSize);
        return $this->app['twig']->render('todos.html', $data);
    }
}
