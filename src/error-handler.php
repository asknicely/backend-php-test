<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->error(function (\Exception $e, $code) use ($app) {
	switch ($code) {
		case 404:
			$subRequest = Request::create('/404');
			$response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
			break;
	
		default:
			$subRequest = Request::create('/500');
			$response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
			break;
	}
	return $response;
});