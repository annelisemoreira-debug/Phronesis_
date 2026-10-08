<?php
session_start();

// Se o usuário NÃO estiver logado, manda ele para a tela de login
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit(); // Interrompe a execução do script
}

include '../conexao.php';

$id = $_SESSION['id'];
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// --- BUSCA O QUE O USUÁRIO JÁ FAVORITOU ---
$meus_favs = [];
$res_f = $conn->query("SELECT video_id FROM favoritos WHERE id_usuario = $id");
while ($f = $res_f->fetch_assoc()) {
    $meus_favs[] = $f['video_id'];
}

// --- BUSCA O QUE O USUÁRIO JÁ ASSISTIU (VISTO) ---
$meus_vistos = [];
$res_v = $conn->query("SELECT video_id FROM vistos WHERE id_usuario = $id");
if ($res_v) {
    while ($v = $res_v->fetch_assoc()) {
        $meus_vistos[] = $v['video_id'];
    }
}

// --- CONFIGURAÇÃO API YOUTUBE ---
require_once __DIR__ . '/../vendor/autoload.php';

$minha_chave = 'AIzaSyCENf31FjtasIsEqd20BTK53vrD_v3GuxQ';
$client = new Google\Client();
$client->setDeveloperKey($minha_chave);
$youtube = new Google\Service\YouTube($client);

$termoBuscaOriginal = isset($_GET['search']) ? $_GET['search'] : (isset($_GET['materia']) ? $_GET['materia'] : 'Biologia');
$termoBuscaAPI = $termoBuscaOriginal . ' aula vestibular';

$modoEconomico = false;

try {
    if (!$modoEconomico) {
        $res = $youtube->search->listSearch('snippet', [
            'q'                 => $termoBuscaAPI,
            'maxResults'        => 12,
            'type'              => 'video',
            'videoDuration'     => 'medium',
            'relevanceLanguage' => 'pt'
        ]);
        $videos = $res['items'];
    } else {
        throw new Exception("Modo Econômico");
    }
} catch (Exception $e) {
    $videos = [
        ['id' => ['videoId' => '0P-fO8P9mSg'], 'snippet' => ['title' => 'Introdução à ' . $termoBuscaOriginal]],
        ['id' => ['videoId' => '9I86L8uWbYk'], 'snippet' => ['title' => 'Revisão Geral de ' . $termoBuscaOriginal]],
        ['id' => ['videoId' => 'vG0Ym-LUpfU'], 'snippet' => ['title' => '5 Dicas de Interpretação | Prof. Noslen']],
        ['id' => ['videoId' => '8mS_E0-m7fU'], 'snippet' => ['title' => 'Como aprender ' . $termoBuscaOriginal]]
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiais Complementares - Phronesis</title>
    <link rel="stylesheet" href="../assets/css/material.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">

    <script>
        (function () {
            const savedTheme = localStorage.getItem('site-theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>

    <style>
        .video-title-link {
            text-decoration: none;
            color: inherit;
        }
        .video-title-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php include '../interface/header.php'; ?>

    <section class="banner">
        <img src="../assets/img/banners/normalMateriais.webp" class="banner-img banner-light" alt="Banner">
        <img src="../assets/img/banners/escuroMateriais.webp" class="banner-img banner-dark" alt="Banner">
    </section>
    
    <div class="wrapper">
        <aside class="menuSidebar">
            <nav>
                <a class="<?= $termoBuscaOriginal == 'Linguagens' ? 'active-subject' : '' ?>" href="?materia=Linguagens">Linguagens</a>
                <a class="<?= $termoBuscaOriginal == 'Matematica' ? 'active-subject' : '' ?>" href="?materia=Matematica">Matemática</a>
                <a class="<?= $termoBuscaOriginal == 'Fisica' ? 'active-subject' : '' ?>" href="?materia=Fisica">Física</a>
                <a class="<?= $termoBuscaOriginal == 'Biologia' ? 'active-subject' : '' ?>" href="?materia=Biologia">Biologia</a>
                <a class="<?= $termoBuscaOriginal == 'Quimica' ? 'active-subject' : '' ?>" href="?materia=Quimica">Química</a>
                <a class="<?= $termoBuscaOriginal == 'Historia' ? 'active-subject' : '' ?>" href="?materia=Historia">História</a>
                <a class="<?= $termoBuscaOriginal == 'Geografia' ? 'active-subject' : '' ?>" href="?materia=Geografia">Geografia</a>

                <div class="sidebar-exam-container">
                    <h3 class="widget-title-sidebar">
                        <span class="titulo-main">Contagem regressiva</span>
                        <span class="tag-vestibulares">VESTIBULARES</span>
                    </h3>
                    <div class="exam-card-mini mini-enem"><span class="exam-name-mini">ENEM 2026</span><div id="timer-enem" class="countdown-mini">00d 00h 00min</div></div>
                    <div class="exam-card-mini mini-fuvest"><span class="exam-name-mini">FUVEST 2027</span><div id="timer-fuvest" class="countdown-mini">00d 00h 00min</div></div>
                    <div class="exam-card-mini mini-unicamp"><span class="exam-name-mini">UNICAMP 2026</span><div id="timer-unicamp" class="countdown-mini">00d 00h 00min</div></div>
                    <div class="exam-card-mini mini-unesp"><span class="exam-name-mini">UNESP 2027</span><div id="timer-unesp" class="countdown-mini">00d 00h 00min</div></div>
                </div>
            </nav>
        </aside>

        <div class="principalContent">
            <main class="areaContent">
                <section class="container-materiais">
                    <div class="video-container-white">
                        <div class="video-header">
                            <h2 class="video-title">Materiais Complementares (<?= htmlspecialchars($termoBuscaOriginal) ?>)</h2>
                            <div class="search-box">
                                <form action="" method="GET" style="display: flex; width: 100%; align-items: center;">
                                    <input type="text" name="search" placeholder="Pesquisar vídeo..." id="searchInput" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" style="background: none; border: none; cursor: pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="video-grid" id="videoGrid">
                            <?php foreach ($videos as $video):
                                $vId = $video['id']['videoId'];
                                $vTitle = $video['snippet']['title'];
                                $isFav = in_array($vId, $meus_favs);
                                $isSeen = in_array($vId, $meus_vistos);
                            ?>
                                <div class="video-card" data-id="<?= $vId ?>">
                                    <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0;">
                                        <iframe class="video-frame" src="https://www.youtube.com/embed/<?= $vId ?>" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                    <p class="video-title-card <?= $isSeen ? 'video-visto' : '' ?>">
                                        <a href="https://www.youtube.com/watch?v=<?= $vId ?>" target="_blank" class="video-title-link link-detectavel">
                                            <?= htmlspecialchars(mb_strimwidth($vTitle, 0, 45, "...")) ?>
                                        </a>
                                        <span class="btn-actions-group">
                                            <button class="seen-btn <?= $isSeen ? 'seen-on' : 'seen-off' ?>">
                                                ✔
                                            </button>
                                            <button class="fav-btn <?= $isFav ? 'star-on' : 'star-off' ?>" onclick="toggleFav('<?= $vId ?>', '<?= addslashes($vTitle) ?>', this)">
                                                ★
                                            </button>
                                        </span>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="caixa-favoritos-nova">
                        <div class="video-header">
                            <h2 class="video-title titulo-salvos">Meus Vídeos Salvos ★</h2>
                        </div>
                        <div class="video-grid" id="listaFavoritos">
                            <?php
                            $res_salvos = $conn->query("SELECT * FROM favoritos WHERE id_usuario = $id");
                            if ($res_salvos->num_rows > 0):
                                while ($f = $res_salvos->fetch_assoc()): 
                                    $isSeenFav = in_array($f['video_id'], $meus_vistos);
                                ?>
                                    <div class="video-card" data-id="<?= $f['video_id'] ?>">
                                        <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0;">
                                            <iframe class="video-frame" src="https://www.youtube.com/embed/<?= $f['video_id'] ?>" frameborder="0" allowfullscreen></iframe>
                                        </div>
                                        <p class="video-title-card <?= $isSeenFav ? 'video-visto' : '' ?>">
                                            <a href="https://www.youtube.com/watch?v=<?= $f['video_id'] ?>" target="_blank" class="video-title-link link-detectavel">
                                                <?= htmlspecialchars(mb_strimwidth($f['titulo_video'], 0, 45, "...")) ?>
                                            </a>
                                            <span class="btn-actions-group">
                                                <button class="seen-btn <?= $isSeenFav ? 'seen-on' : 'seen-off' ?>">
                                                    ✔
                                                </button>
                                                <button class="fav-btn star-on" onclick="toggleFav('<?= $f['video_id'] ?>', '<?= addslashes($f['titulo_video']) ?>', this)">
                                                    ★
                                                </button>
                                            </span>
                                        </p>
                                    </div>
                                <?php endwhile;
                            else: ?>
                                <p class="sem-favoritos" style="grid-column: 1/-1; text-align: center; color: #999; padding: 20px;">Você ainda não favoritou vídeos.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

<script src="../assets/js/lib/material.js" defer></script>

<?php include '../perfil/vlibras.php'; ?>

</body>
</html>