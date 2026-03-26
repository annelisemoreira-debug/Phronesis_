<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;

$client = new Client();
$client->setAuthConfig('credentials.json');
$client->setScopes(Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');

$tokenPath = 'token.json';
if (file_exists($tokenPath)) {
    $accessToken = json_decode(file_get_contents($tokenPath), true);
    $client->setAccessToken($accessToken);
}

if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
        // Se rodar via terminal, ele pede o código aqui
        $authUrl = $client->createAuthUrl();
        echo "Abra este link: " . $authUrl . "\n";
        echo "Cole o código aqui: ";
        $authCode = trim(fgets(STDIN));
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);
    }
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
}

$service = new Calendar($client);