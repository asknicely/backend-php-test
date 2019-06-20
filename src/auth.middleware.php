<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

$app->before(function (Request $request, Application $app) {
    $user = $app['session']->get('user');
    $whiteList = array('/', '/login');
    if (
        null === $user
        && !in_array($request->getPathInfo(), $whiteList)
    ) {
        return new RedirectResponse('/login');
    }
});