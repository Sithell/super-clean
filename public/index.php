<?php

declare(strict_types=1);

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Slim\App;
use Slim\Http\StatusCode;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/google.php';

$config = include('../config/app.php');

$app = new App;

$app->post('/order', function (Request $request, Response $response, array $args) use ($config) {
    $body = $request->getParsedBody();
    try {
        sendMessage(
            $config['mail']['from'],
            $config['mail']['to'],
            $config['mail']['subject'],
            json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        );
        $response = $response->withJson(['status' => 'ok'], StatusCode::HTTP_OK);
    } catch (Exception $e) {
        $response = $response
            ->withJson(
                [
                    'status' => 'error',
                    'code' => $e->getCode(),
                    'detail' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                StatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
    }
    return $response;
});

$app->run();
