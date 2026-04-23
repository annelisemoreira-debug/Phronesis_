<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

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
    <link rel="stylesheet" href="../assets/css/meuCalendario.css">

</head>
<body>
    <img src="../assets/img/fundoAmarelo01.png" class="bg-html" alt="Fundo">

    <div class="login-card">
    <h2>Conectar para criar!</h2>
    <p>Para facilitar a organização da sua rotina, o nosso site funciona de forma integrada com o <span class="google-brand">Google Agenda</span>.</p>
    
    <p>Para que você consiga montar e visualizar seu cronograma personalizado por aqui, você só precisa conectar a sua conta do Google. Assim que você fizer o login, sincronizamos tudo automaticamente para você.</p>
    
    <a class="btn-google" href="<?= $authUrl ?>">
        Entrar com Google
    </a>

    <div class="footer-link">
        <a href="../index.php">Deseja Voltar?</a>
    </div>
</div>
        
</body>
</html>