<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * @param $sender string sender email address
 * @param $to string recipient email address
 * @param $subject string email subject
 * @param $messageText string email text
 * @throws \Google\Exception
 */
function sendMessage(string $sender, string $to, string $subject, string $messageText): void
{
    $message = new Google_Service_Gmail_Message();

    $rawMessageString = "From: <{$sender}>\r\n";
    $rawMessageString .= "To: <{$to}>\r\n";
    $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
    $rawMessageString .= "MIME-Version: 1.0\r\n";
    $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
    $rawMessageString .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
    $rawMessageString .= "{$messageText}\r\n";

    $rawMessage = strtr(base64_encode($rawMessageString), array('+' => '-', '/' => '_'));
    $message->setRaw($rawMessage);

    $client = getClient();
    $service = new Google_Service_Gmail($client);
    $service->users_messages->send('me', $message);
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 * @throws \Google\Exception
 */
function getClient(): Google_Client
{
    $client = new Google_Client();
    $client->setApplicationName('Super Clean Mail Sender');
    $client->setScopes(Google_Service_Gmail::GMAIL_SEND);
    $client->setAuthConfig('../config/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = '../config/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}
