<?php

declare(strict_types=1);

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Slim\App;
use Slim\Http\StatusCode;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/google.php';
require __DIR__ . '/../lib/utils.php';

$config = include('../config/app.php');

$app = new App;

$app->post('/order', function (Request $request, Response $response, array $args) use ($config) {
    $body = $request->getParsedBody();
    $city = $body['city'] ?? '-';
    $phone = $body['phone'] ?? '-';
    $typeOfCleaning = $body['type_of_cleaning'] ?? '-';
    $plan = $body['plan'] ?? '-';
    $totalPrice = (int)($body['total_price'] ?? 0);
    $comment = $body['comment'] ?? 'нет';

    $street = $body['street'] ?? '';
    $house = $body['house'] ?? '';
    $flat = $body['flat'] ?? null;
    $address = '-';
    if (!empty($street)) {
        $address = $street;
        if (!empty($house)) {
            $address .= ' д. ' . $house;
            if (!empty($flat)) {
                $address .= ' кв. ' . strval($flat);
            }
        }
    }

    $extras = (array)($body['extras'] ?: []);
    $extrasParsed = [];
    foreach ($extras as $extra) {
        $extrasParsed[] = $extra['name'] . ' x' . $extra['quantity'];
    }

    $date = date('d.m.y H:i');
    $raw = json_encode($body);

    $messageText = generateLetter(
        $city,
        $phone,
        $address,
        $plan,
        $typeOfCleaning,
        $extrasParsed,
        $comment,
        $totalPrice,
        $raw,
        $date
    );

    try {
        sendMessage(
            $config['mail']['from'],
            $config['mail']['to'],
            $config['mail']['subject'],
            $messageText
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
