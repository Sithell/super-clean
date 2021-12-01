<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '../lib/google.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Gmail($client);
