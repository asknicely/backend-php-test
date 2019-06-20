<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

$app->before(function (Request $request, Application $app) {
    $user = $app['session']->get('user');
    
    if (
        null === $user
        && $request->getPathInfo() !== '/login'
    ) {
        return new RedirectResponse('/login');
    }
});