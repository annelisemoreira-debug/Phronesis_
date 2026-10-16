<?php
session_start();

// 1. Verifica se a variável de sessão existe (Usuário logado no sistema)
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$id = intval($_SESSION['id']);
$res = $conn->query("SELECT foto, google_token FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy(); // Destrói a sessão inválida
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Inicializa o Cliente Google API
$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->setAccessType('offline');
// Importante: 'consent' garante que o Google devolva o Refresh Token permanente
$client->setPrompt('consent'); 

// Link de redirecionamento idêntico ao do Console do Google Developer
$redirect_uri = 'http://localhost/Phronesis_/pages/meu_calendario.php';
$client->setRedirectUri($redirect_uri);

// ======================================================================
// CONTROLE DE TOKEN: RECUPERAÇÃO DO BANCO OU SESSÃO
// ======================================================================

// 1. Se a sessão está limpa mas temos o token salvo no Banco de Dados, joga ele para a sessão
if (!isset($_SESSION['upload_token']) && !empty($dadosUser['google_token'])) {
    $_SESSION['upload_token'] = json_decode($dadosUser['google_token'], true);
}

// 2. Se o Google acabou de mandar o código de autorização de volta pela URL
if (isset($_GET['code'])) {
    try {   
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['upload_token'] = $token;
        
        // SALVA NO BANCO: Transforma o array do token em texto JSON e guarda no perfil do usuário
        $tokenJson = $conn->real_escape_string(json_encode($token));
        $conn->query("UPDATE usuario SET google_token = '$tokenJson' WHERE id_usuario = $id");

        header('Location: cronograma.php');
        exit;
    } catch (Exception $e) {
        // Silencia ou trata o erro se o código expirar
    }
}

// 3. Validação final de redirecionamento
if (isset($_SESSION['upload_token'])) {
    $client->setAccessToken($_SESSION['upload_token']);
    
    // Se o token expirou, mas temos o Refresh Token (gerado pelo accessType offline), ele renova sozinho!
    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $_SESSION['upload_token'] = $newToken;
            
            // Atualiza o novo token gerado no banco de dados
            $tokenJson = $conn->real_escape_string(json_encode($newToken));
            $conn->query("UPDATE usuario SET google_token = '$tokenJson' WHERE id_usuario = $id");
        }
    }

    // Se o token estiver perfeitamente ativo e válido, pula direto para o cronograma
    if (!$client->isAccessTokenExpired()) {
        header('Location: cronograma.php');
        exit();
    }
}

// Se chegou até aqui, o usuário realmente não tem vinculação ativa. Mostra a tela.
$authUrl = $client->createAuthUrl();
$googleLogado = 'false';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sincronizar Agenda - Phronesis</title>
    <link rel="stylesheet" href="../assets/css/meuCalendario.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
                (function () {
            const savedTheme = localStorage.getItem('site-theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
</head>
<body>
    
    <img src="../assets/img/fundos/fundoAmarelo02.webp" class="bg-html" alt="Fundo">
    

    <div class="login-card">
        <h2>Conectar para criar!</h2>
        <p>Para facilitar a organização da sua rotina, o nosso site funciona de forma integrada com o <span class="google-brand">Google Agenda</span>.</p>
        
        <p>Sincronize sua conta Google para criar e visualizar seu cronograma automaticamente.</p>
        
        <a class="btn-google" href="<?= $authUrl ?>">
            <i class="fa-brands fa-google"></i> Vincular Google Agenda
        </a>

        <div class="footer-link">
            <a href="../index.php">Voltar ao Início</a>
        </div>
    </div>

<script src="../assets/js/lib/meu_calendario.js" defer></script>

    <?php include '../perfil/vlibras.php'; ?>
        
</body>
</html>