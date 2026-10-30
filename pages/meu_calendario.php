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
$res = $conn->query("SELECT foto, google_token, google_email FROM usuario WHERE id_usuario = $id");
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
$client->addScope('email'); // NOVO: permite descobrir o e-mail da conta Google
$client->setAccessType('offline');
// Importante: 'consent' garante que o Google devolva o Refresh Token permanente
$client->setPrompt('consent');

// Link de redirecionamento idêntico ao do Console do Google Developer
$redirect_uri = 'http://localhost/Phronesis_/pages/meu_calendario.php';
$client->setRedirectUri($redirect_uri);

// ======================================================================
// FUNÇÕES AUXILIARES
// ======================================================================

/**
 * Pergunta ao Google qual é o e-mail da conta autenticada.
 * Retorna null se o token não tiver o escopo de e-mail (token antigo).
 */
function obterEmailGoogle(Google\Client $client): ?string
{
    try {
        $oauth = new Google\Service\Oauth2($client);
        $email = $oauth->userinfo->get()->getEmail();
        return $email ? strtolower(trim($email)) : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Grava token + e-mail no usuário e "adota" os cronogramas antigos
 * (criados antes desta funcionalidade, com google_email NULL).
 */
function vincularGoogle(mysqli $conn, int $idUser, array $token, string $email): void
{
    $json = json_encode($token);

    $st = $conn->prepare("UPDATE usuario SET google_token = ?, google_email = ? WHERE id_usuario = ?");
    $st->bind_param('ssi', $json, $email, $idUser);
    $st->execute();
    $st->close();

    $st = $conn->prepare("UPDATE cronograma SET google_email = ? WHERE id_usuario = ? AND google_email IS NULL");
    $st->bind_param('si', $email, $idUser);
    $st->execute();
    $st->close();

    $_SESSION['google_email'] = $email;
}

// ======================================================================
// CONTROLE DE TOKEN: RECUPERAÇÃO DO BANCO OU SESSÃO
// ======================================================================

// 1. Sessão limpa, mas token salvo no banco: restaura para a sessão
if (!isset($_SESSION['upload_token']) && !empty($dadosUser['google_token'])) {
    $_SESSION['upload_token'] = json_decode($dadosUser['google_token'], true);
}

// 2. O Google acabou de devolver o código de autorização
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (!isset($token['error'])) {
            $client->setAccessToken($token);
            $email = obterEmailGoogle($client);

            if ($email) {
                $_SESSION['upload_token'] = $token;
                vincularGoogle($conn, $id, $token, $email);
                header('Location: cronograma.php');
                exit;
            }
        }
    } catch (Exception $e) {
        // código expirado ou inválido: cai na tela de vínculo
    }
}

// 3. Validação do token existente
if (isset($_SESSION['upload_token'])) {
    $client->setAccessToken($_SESSION['upload_token']);

    // Token expirado: renova com o Refresh Token (se existir)
    if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
        $refreshAntigo = $client->getRefreshToken();
        $newToken = $client->fetchAccessTokenWithRefreshToken($refreshAntigo);

        if (!isset($newToken['error'])) {
            // O Google nem sempre devolve o refresh_token de novo: preserva o antigo
            if (empty($newToken['refresh_token'])) {
                $newToken['refresh_token'] = $refreshAntigo;
            }
            $_SESSION['upload_token'] = $newToken;
            $client->setAccessToken($newToken);
        }
    }

    if (!$client->isAccessTokenExpired()) {
        $email = $_SESSION['google_email'] ?? ($dadosUser['google_email'] ?? null);
        if (!$email) {
            $email = obterEmailGoogle($client);
        }

        if ($email) {
            vincularGoogle($conn, $id, $_SESSION['upload_token'], $email);
            header('Location: cronograma.php');
            exit();
        }

        // Token antigo, sem escopo de e-mail: descarta e pede nova autorização
        unset($_SESSION['upload_token'], $_SESSION['google_email']);
        $conn->query("UPDATE usuario SET google_token = NULL, google_email = NULL WHERE id_usuario = $id");
        $client->setAccessToken([]);
    }
}

// Sem vínculo ativo: mostra a tela de conexão
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

        <p>Seus cronogramas ficam ligados à <b>conta Google</b>: se você entrar no Phronesis com outra conta, mas vincular o mesmo e-mail do Google, eles aparecem aqui também.</p>

        <a class="btn-google" href="<?= htmlspecialchars($authUrl) ?>">
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