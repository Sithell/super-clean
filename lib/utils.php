<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * @throws Exception
 */
function generateLetter(
    string $city,
    string $phone,
    string $address,
    string $area,
    string $type,
    array $extras,
    string $comment,
    int $price,
    string $raw,
    string $date
): string
{
    if (!$template = file_get_contents(__DIR__ . '/../templates/order.min.html')) {
        throw new Exception("File templates/order.min.html not found");
    }

    $template = preg_replace('{{city}}', $city, $template);
    $template = preg_replace('{{phone}}', $phone, $template);
    $template = preg_replace('{{address}}', $address, $template);
    $template = preg_replace('{{area}}', $area, $template);
    $template = preg_replace('{{type}}', $type, $template);
    $template = preg_replace('{{comment}}', $comment, $template);
    $template = preg_replace('{{price}}', strval($price), $template);
    $template = preg_replace('{{raw}}', $raw, $template);
    $template = preg_replace('{{date}}', $date, $template);

    $extraString = '';
    foreach ($extras as $extra) {
        $extraString .= "<li>$extra</li>";
    }
    $template = preg_replace('{{extra}}', $extraString, $template);

    return $template;
}
