<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';
require_once __DIR__ . '/_video_card.php';

$id = (int) $_SESSION['id'];

// Confirma que o usuário da sessão ainda existe
$stmt = $conn->prepare("SELECT foto FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$dadosUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Lista de video_id de uma tabela do usuário (favoritos / vistos)
function idsDoUsuario($conn, $tabela, $id) {
    $permitidas = ['favoritos', 'vistos'];
    if (!in_array($tabela, $permitidas, true)) return [];
    $stmt = $conn->prepare("SELECT video_id FROM $tabela WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return array_column($rows, 'video_id');
}

$meus_favs   = idsDoUsuario($conn, 'favoritos', $id);
$meus_vistos = idsDoUsuario($conn, 'vistos', $id);

// --- API YOUTUBE ---
require_once __DIR__ . '/../vendor/autoload.php';

// Guarde a chave fora do código (variável de ambiente) — veja observações
$minha_chave = getenv('YOUTUBE_API_KEY') ?: 'AIzaSyCENf31FjtasIsEqd20BTK53vrD_v3GuxQ';
$client = new Google\Client();
$client->setDeveloperKey($minha_chave);
$youtube = new Google\Service\YouTube($client);

$busca = isset($_GET['search']) ? trim(mb_substr($_GET['search'], 0, 80)) : '';
$materiaAtiva = $busca === '' ? ($_GET['materia'] ?? 'Biologia') : null;
$termoBuscaOriginal = $busca !== '' ? $busca : $materiaAtiva;
$termoBuscaAPI = $termoBuscaOriginal . ' aula vestibular';

$modoEconomico = false;

try {
    if ($modoEconomico) throw new Exception("Modo Econômico");
    $res = $youtube->search->listSearch('snippet', [
        'q'                 => $termoBuscaAPI,
        'maxResults'        => 18,
        'type'              => 'video',
        'videoDuration'     => 'medium',
        'relevanceLanguage' => 'pt'
    ]);
    $videos = $res['items'];
} catch (Exception $e) {
    $videos = [
        ['id' => ['videoId' => '0P-fO8P9mSg'], 'snippet' => ['title' => 'Introdução à ' . $termoBuscaOriginal]],
        ['id' => ['videoId' => '9I86L8uWbYk'], 'snippet' => ['title' => 'Revisão Geral de ' . $termoBuscaOriginal]],
        ['id' => ['videoId' => 'vG0Ym-LUpfU'], 'snippet' => ['title' => '5 Dicas de Interpretação | Prof. Noslen']],
        ['id' => ['videoId' => '8mS_E0-m7fU'], 'snippet' => ['title' => 'Como aprender ' . $termoBuscaOriginal]]
    ];
}

$paginaAtual = 'material';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiais Complementares - Phronesis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/material.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.webp">

    <script>
        (function () {
            if ((localStorage.getItem('site-theme') || 'light') === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
            document.documentElement.classList.add('font-' + (localStorage.getItem('site-font-size') || 'normal'));
        })();
    </script>
</head>
<body data-pagina="material">
    <?php include '../interface/header.php'; ?>
    

    <section class="banner">
        <img src="../assets/img/banners/normalMateriais.webp" class="banner-img banner-light" alt="Banner Materiais">
        <img src="../assets/img/banners/escuroMateriais.webp" class="banner-img banner-dark" alt="Banner Materiais Escuro">
    </section>

    <!-- nada entre o banner e o wrapper -->

    <div class="wrapper">
        <?php include __DIR__ . '/_material_sidebar.php'; ?>

        <div class="principalContent">
            <main class="areaContent">

                <!-- ÚNICO cabeçalho: título + busca, dentro do card dos vídeos -->
                <div class="video-header">
                    <h1 class="video-title">Materiais Complementares (<?= e($termoBuscaOriginal) ?>)</h1>

                    <form class="busca-form" id="buscaForm" action="material.php" method="GET" role="search">
                        <button type="submit" class="busca-toggle" aria-label="Pesquisar vídeo">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <input type="search" name="search" id="searchInput" placeholder="Pesquisar vídeo..."
                               maxlength="80" autocomplete="off" value="<?= e($busca) ?>">
                    </form>
                </div>

                <div class="video-scroll" id="videoScroll">
                    <div class="video-grid" id="videoGrid" data-lista>
                        <?php foreach ($videos as $video):
                            $vId = $video['id']['videoId'];
                            renderVideoCard($vId, $video['snippet']['title'], in_array($vId, $meus_favs, true), in_array($vId, $meus_vistos, true));
                        endforeach; ?>
                    </div>

                    <div class="mais-wrap" id="maisWrap" hidden>
                        <button type="button" class="btn-mais" id="btnMais">
                            Mostrar mais <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/_bottom_nav.php'; ?>

    <script src="../assets/js/lib/material.js" defer></script>
    <?php include '../perfil/vlibras.php'; ?>
    <?php include '../interface/footer.php';?>
</body>
</html>

