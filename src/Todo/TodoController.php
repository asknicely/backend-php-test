<?php

namespace Todo;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class TodoController
{

    protected $app;
    protected $user;

    public function __construct($app)
    {
        $this->app = $app;
        $this->user = $app['session']->get('user');

    }

    /*
     * check if user is login
     */

    public function get($id, $type)
    {

        //define name
        $template = "";
        $pagnation = null;
        $limit = 10;
        // get one task
        if ($id) {
            $template = "todo";
            $query = $this->app['db.builder']->select('*')->from('todos')->where('id =?')->andWhere('user_id=?')
                ->setParameter(0, $id)->setParameter(1, $this->user->id);
            $todo = $query->execute()->fetchObject();
            // if is json format request
            if ($todo) {
                if ($type == "json")
                    return json_encode($todo);
            } else {
                $this->app['session']->getFlashBag()->add('error', 'Task not found');
                return $this->app->redirect('/todo');
            }
        } else {
            $template = "todos";
            $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
            $page = isset($_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 0;
            $query = $this->app['db.builder']->select('count(id) as total')->from('todos')->where('user_id =?')
                ->setParameter(0, $this->user->id);
            $sum = $query->execute()->fetchObject();
            if ($sum) {
                $pageNum = ceil($sum->total / $limit);
            }
            $index = ($page - 1) > 0 ? $page - 1 : 0;
            $query = $this->app['db.builder']->select('*')->from('todos')->where('user_id =?')
                ->setParameter(0, $this->user->id)->setFirstResult($index * $limit)->setMaxResults($limit);
            $todos = $query->execute()->fetchAll();
            $pagnation = $this->createLinks(5, "pagination", $sum->total, $limit, $page);


        }
        return $this->app['twig']->render($template . '.html', [
            $template => ${$template},
            'pagnation' => $pagnation,
            'limit' => $limit
        ]);

    }

    /*
     *  how many show per line
     *
     */
    protected function createLinks($links, $list_class, $total, $limit, $page)
    {

        if ($limit == 'all') {
            return '';
        }

        $last = ceil($total / $limit);

        $start = (($page - $links) > 0) ? $page - $links : 1;
        $end = (($page + $links) < $last) ? $page + $links : $last;

        $html = '<ul class="' . $list_class . '">';

        $class = ($page == 1) ? "disabled" : "";
        if ($class == "") {
            $html .= '<li class="' . $class . '"><a href="?limit=' . $limit . '&page=' . ($page - 1) . '">&laquo;</a></li>';
        } else {
            $html .= '<li class="' . $class . '"><a href="#">&laquo;</a></li>';
        }


        if ($start > 1) {
            $html .= '<li><a href="?limit=' . $limit . '&page=1">1</a></li>';
            $html .= '<li class="disabled"><span>...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ($page == $i) ? "active" : "";
            $html .= '<li class="' . $class . '"><a href="?limit=' . $limit . '&page=' . $i . '">' . $i . '</a></li>';
        }

        if ($end < $last) {
            $html .= '<li class="disabled"><span>...</span></li>';
            $html .= '<li><a href="?limit=' . $limit . '&page=' . $last . '">' . $last . '</a></li>';
        }

        $class = ($page == $last) ? "disabled" : "";
        if ($class == "") {
            $html .= '<li class="' . $class . '"><a href="?limit=' . $limit . '&page=' . ($page + 1) . '">&raquo;</a></li>';
        } else {
            $html .= '<li class="' . $class . '"><a href="#">&raquo;</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }


    public function add(Request $request)
    {

        $description = $request->get('description');
        //check description is set and not white space
        if ($description && trim($description) != "") {
            $query = $this->app['db.builder']->insert('todos')->values(['user_id' => '?', 'description' => "?"])->setParameters([0 => $this->user->id, 1 => $description]);
            if ($query->execute()) {
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
                ->where('id = ?')->andWhere('user_id=?')->setParameters([0 => $id, 1 => $this->user->id]);
            // success return flash
            if ($query->execute()) {
                $this->app['session']->getFlashBag()->add('success', 'Success: Mark Task Completed');
            }
        }
        return $this->app->redirect('/todo');
    }

    public function delete($id)
    {

        if ($id) {
            $query = $this->app['db.builder']->delete('todos')->where('id =?')->andWhere('user_id=?')->setParameter(0, $id)->setParameter(1, $this->user->id);
            if ($query->execute()) {
                $this->app['session']->getFlashBag()->add('success', 'Success: Task delete');
            }
        }
        return $this->app->redirect('/todo');
    }
}
