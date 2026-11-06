<?php
/**
 * pages/meu_calendario.php
 * Tela de vínculo com o Google Agenda.
 *
 * Fluxo:
 *  1. Garante que o usuário está logado no Phronesis.
 *  2. Restaura/renova o token do Google (sessão ou banco).
 *  3. Se já houver vínculo válido, redireciona para o cronograma.
 *  4. Caso contrário, mostra a tela "Conectar para criar!".
 */

session_start();

// ======================================================================
// 1. VERIFICAÇÃO DE LOGIN
// ======================================================================

// Usuário não logado no sistema: volta para o login
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

// Confirma que o ID da sessão ainda é válido no banco de dados
$id  = intval($_SESSION['id']);
$res = $conn->query("SELECT foto, google_token, google_email FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy(); // Destrói a sessão inválida
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

// ======================================================================
// 2. CLIENTE GOOGLE API
// ======================================================================
$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->addScope('email'); // Permite descobrir o e-mail da conta Google
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

    // Salva o token e o e-mail na conta do usuário
    $st = $conn->prepare("UPDATE usuario SET google_token = ?, google_email = ? WHERE id_usuario = ?");
    $st->bind_param('ssi', $json, $email, $idUser);
    $st->execute();
    $st->close();

    // Vincula os cronogramas antigos (sem e-mail) a esta conta Google
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
        // Código expirado ou inválido: cai na tela de vínculo
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

    // Token válido: confirma o e-mail e segue para o cronograma
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

// ======================================================================
// SEM VÍNCULO ATIVO: MOSTRA A TELA DE CONEXÃO
// ======================================================================
$authUrl = $client->createAuthUrl();
$googleLogado = 'false';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar Agenda - Phronesis</title>

    <!-- Fonte Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <!-- Ícones (barra inferior) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos: header do site e desta página -->
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/meu_calendario.css">

    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.webp">

    <!-- SweetAlert (avisos do meu_calendario.js) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Aplica o tema escuro antes de renderizar (evita flash branco) -->
    <script>
        (function () {
            if ((localStorage.getItem('site-theme') || 'light') === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
</head>
<body>

    <!-- ================= HEADER DO SITE ================= -->
    <?php include '../interface/header.php'; ?>

    <!-- ================= CONTEÚDO CENTRAL ================= -->
    <main class="conectar">
        <section class="login-card">
            <h2>Conectar para criar!</h2>

            <!-- "Google Agenda" colorido letra por letra -->
            <p>
                Para facilitar a organização da sua rotina, o nosso site funciona de forma integrada com o
                <span class="google-brand">
                    <span class="c-b">G</span><span class="c-r">o</span><span class="c-y">o</span><span class="c-b">g</span><span class="c-g">l</span><span class="c-r">e</span>
                    <span class="c-r">A</span><span class="c-y">g</span><span class="c-g">e</span><span class="c-b">n</span><span class="c-r">d</span><span class="c-g">a</span>
                </span>.
            </p>

            <p>Sincronize sua conta Google para criar e visualizar seus cronogramas automaticamente.</p>

            <p>
                Seus cronogramas ficam ligados à <b>conta Google</b>, se você entrar no Phronesis com uma
                conta diferente, você também poderá acessá-los. Isso garante uma experiência ainda mais completa.
            </p>

            <!-- Botão que leva à autorização do Google -->
            <a class="btn-google" href="<?= htmlspecialchars($authUrl) ?>">
                <!-- Logo colorido do Google -->
                <svg class="g-logo" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.5 24c0-1.61-.15-3.16-.42-4.69H24v9.07h12.75c-.55 2.94-2.22 5.44-4.72 7.12l7.31 5.67C43.62 36.6 46.5 30.9 46.5 24z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.31-5.67c-2.03 1.36-4.63 2.18-8.58 2.18-6.26 0-11.57-4.22-13.46-9.91l-7.97 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Vincular Google Agenda
            </a>

            <div class="footer-link">
                <a href="../index.php">Voltar ao Início</a>
            </div>
        </section>
    </main>

    <!-- Script da página (tema, fonte e avisos) -->
    <script src="../assets/js/lib/meu_calendario.js" defer></script>

    <!-- ================= NAVEGAÇÃO INFERIOR (tablet e celular) ================= -->
    <nav class="bottom-nav" aria-label="Navegação principal">
        <a href="../index.php" class="bn-item">
            <i class="fa-solid fa-house"></i><span>Início</span>
        </a>
        <a href="livros.php" class="bn-item">
            <i class="fa-solid fa-book"></i><span>Biblioteca</span>
        </a>
        <a href="material.php" class="bn-item">
            <i class="fa-solid fa-tv"></i><span>Material</span>
        </a>
        <a href="cronograma.php" class="bn-item ativo" aria-current="page">
            <i class="fa-regular fa-calendar-days"></i><span>Cronogramas</span>
        </a>
        <a href="../perfil/configperfil.php" class="bn-item">
            <i class="fa-regular fa-user"></i><span>Perfil</span>
        </a>
    </nav>

    <!-- VLibras e footer do site -->
    <?php include '../perfil/vlibras.php'; ?>
    <?php include '../interface/footer.php'; ?>

</body>
</html>