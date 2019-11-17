<?php
namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;
use Twig\Environment;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * AuthController
 *
 * @package Controllers
 * @todo get rid of plain mysql queries
 * @todo create a repository
 * @todo validate request data in custom Request objects
 * @todo move all the login from the controller
 */
class AuthController extends Controller
{
    /**
     * Logout
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $this->getSession()->set('user', null);
        return $this->app->redirect('/login');
    }

    /**
     * Login form
     *
     * @return string
     */
    public function loginForm(): string
    {
        return $this->getTwig()->render('login.html', []);
    }

    /**
     * Login
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = password_hash(md5($request->get('password')), PASSWORD_BCRYPT);

        // @todo validation should be done in a custom request class
        if (!empty($username) && !empty($password)) {
            // @todo all queries should be done in a repository
            $sql = "SELECT * FROM users WHERE username = '{$username}'";
            $user = $this->getConnection()->fetchAssoc($sql);

            if (!empty($user) && password_verify($user['password'], $password)) {
                $this->getSession()->set('user', $user);
                return $this->app->redirect('/todo');
            }
        }

        return $this->app->redirect('/login');
    }
}
