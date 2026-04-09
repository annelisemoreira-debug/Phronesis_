<?php
session_start();
require_once '../vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->setAccessType('offline');
$client->setPrompt('select_account consent'); 

// ESTE LINK ABAIXO TEM QUE SER IGUAL AO QUE VOCÊ SALVOU NO GOOGLE
$redirect_uri = 'http://localhost/Phronesis_/pages/meu_calendario.php';
$client->setRedirectUri($redirect_uri);

// Se o Google enviou o código de volta
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['upload_token'] = $token;
        header('Location: cronograma.php');
        exit;
    } catch (Exception $e) {
        echo "Erro ao obter token: " . $e->getMessage();
    }
}
// ... restante do código do botão

$authUrl = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Cronograma</title>
    <style>
        body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; }
        .login-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .btn-google { background: #4285F4; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Phronesis</h2>
        <p>Conecte sua conta para gerar seu cronograma</p>
        <br>
        <a class="btn-google" href="<?= $authUrl ?>">Entrar com Google</a>
    </div>
</body>
</html>