<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/../credentials.json');
// A URL abaixo deve ser IGUAL à que você colocou no Google Cloud
$client->setRedirectUri('http://localhost/Phronesis_/pages/index.php');
$client->addScope(Google\Service\Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$tokenPath = __DIR__ . '/../token.json';

// 1. Se o Google mandou o código de volta pela URL
if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    file_put_contents($tokenPath, json_encode($accessToken));
    echo "<h1>✅ SUCESSO!</h1>";
    echo "O arquivo <b>token.json</b> foi criado na raiz do projeto.<br>";
    echo "<a href='cronograma.php'>Clique aqui para ver seu Cronograma</a>";
    exit;
}

// 2. Se o arquivo já existe
if (file_exists($tokenPath)) {
    echo "O token já existe. <a href='cronograma.php'>Ir para o Cronograma</a>";
} else {
    // 3. Se não existe e não tem código, mostra o link
    $authUrl = $client->createAuthUrl();
    echo "<h1>Autorização Necessária</h1>";
    echo "<a href='$authUrl' style='padding:10px; background:blue; color:white; text-decoration:none;'>GERAR TOKEN DO GOOGLE</a>";
}