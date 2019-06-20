<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Middleware to attach flash bag session notifications to
 * JSON responses with we recive AJAX requests.
 */
$app->after(function (Request $request, Response $response) use ($app){
    // If it is not an AJAX request or is not a JSON response, just continue
    if (!$request->isXmlHttpRequest() || !($response instanceof JsonResponse)) {
        return $response;
    }

    // Get flash messages
    $flashMessages = $app['session']->getFlashBag()->all();
    if (!empty($flashMessages)) {
        // Decode the JSON response before encoding it again with additional data
        $data = json_decode($response->getContent(), true);
        $data['messages'] = $flashMessages;
        $response->setData($data);
    }
    
    return $response;
});