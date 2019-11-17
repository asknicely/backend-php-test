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
        $password = $request->get('password');

        if (!empty($username) && !empty($password)) {
            $sql = "SELECT * FROM users WHERE username = '{$username}' and password = '{$password}'";
            $user = $this->getConnection()->fetchAssoc($sql);

            if ($user) {
                $this->getSession()->set('user', $user);
                return $this->app->redirect('/todo');
            }
        }

        return $this->app->redirect('/login');
    }
}
