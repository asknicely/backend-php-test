<?php
namespace Todo;
use Symfony\Component\HttpFoundation\Request;


class TodoController
{

    // check login
    protected $app;
    protected $user;
    public function __construct($app)
    {
        $this->app=$app;
        $this->user = $app['session']->get('user');
        if ( $this->user  === null) {
            return $app->redirect('/login');
        }
    }

    public function get($id,$type)
    {
        if (null === $user = $this->app['session']->get('user')) {
            return $this->app->redirect('/login');
        }
        $user_id = $user['id'];
        // there has some issue people can view other peoples item.
        if ($id) {
            $query = $this->app['db.builder']->select('*')->from('todos')->where('id =?')->andWhere('user_id=?')
                ->setParameter(0, $id)->setParameter(1, $user_id);
            $todo = $query->execute()->fetchAll();
            // if is json format request
            if(isset($todo[0])) {
                if ($type == "json")
                    return json_encode($todo[0]);
                else {
                    return $this->app['twig']->render('todo.html', [
                        'todo' => $todo[0],
                    ]);
                }
            }else{
                $this->app['session']->getFlashBag()->add('error', 'Task not found');
                return $this->app->redirect('/todo');
            }
        } else {
            $query = $this->app['db.builder']->select('*')->from('todos')->where('user_id =?')
                ->setParameter(0, $user_id);
            $todos = $query->execute()->fetchAll();
            return $this->app['twig']->render('todos.html', [
                'todos' => $todos,
            ]);
        }
    }
    public function add(Request $request)
    {
        $description = $request->get('description');
        //check description is set and not white space
        if ($description && trim($description) != "") {
            $query = $this->app['db.builder']->insert('todos')->values(['user_id' => '?', 'description' => "?"])->setParameters([0 => $this->user['id'], 1 => $description]);
            if($query->execute()) {
                $this->app['session']->getFlashBag()->add('success', 'Success: Task added');
            }
        } else {
            //send error message
            $this->app['session']->getFlashBag()->add('error', 'Description can not be empty');
        }
        return $this->app->redirect('/todo');
    }

    public function completed($id)
    {
        //if pass id
        if ($id) {
            // if pass id is also user id
            $query = $this->app['db.builder']->update('todos')
                ->set('status', "'Completed'")
                ->where('id = ?')->andWhere('user_id=?')->setParameters([0 => $id, 1 => $this->user['id']]);
            // success return flash
            if($query->execute())
            {
                $this->app['session']->getFlashBag()->add('success', 'Success: Mark Task Completed');
            }
        }
        return $this->app->redirect('/todo');
    }

    public function delete($id)
    {
        if ($id) {
            $query = $this->app['db.builder']->delete('todos')->where('id =?')->andWhere('user_id=?')->setParameter(0, $id)->setParameter(1, $this->user['id']);
            if ($query->execute()) {
                $this->app['session']->getFlashBag()->add('success', 'Success: Task delete');
            }
        }
        return $this->app->redirect('/todo');
    }
}
