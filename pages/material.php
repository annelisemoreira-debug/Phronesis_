<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "phronesis");

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
while($f = $res_f->fetch_assoc()) { $meus_favs[] = $f['video_id']; }

// --- CONFIGURAÇÃO API YOUTUBE (MANTIDA INTEGRALMENTE) ---
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
            'q'             => $termoBuscaAPI,
            'maxResults'    => 12,          
            'type'          => 'video',
            'videoDuration' => 'medium',    
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
</head>
<body>
    <?php include '../interface/header.php'; ?>

    <section class="banner"><img src="../assets/img/bannerAzul.webp" alt=""></section>

    <div class="wrapper">
        <aside class="menuSidebar">
            <div class="logoMarca">
                <a href="../index.php"><img src="../assets/img/logoSite.webp" alt="Logo"></a>
            </div>
            <nav>
                <a href="?materia=Linguagens">Linguagens</a>
                <a href="?materia=Matematica">Matemática</a>
                <a href="?materia=Fisica">Física</a>
                <a href="?materia=Biologia">Biologia</a>
                <a href="?materia=Quimica">Química</a>
                <a href="?materia=Historia">História</a>
                <a href="?materia=Geografia">Geografia</a>

                <div class="sidebar-exam-container">
                    <h3 class="widget-title-sidebar">Contagem Regressiva</h3>
                    <div class="exam-card-mini mini-enem"><span class="exam-name-mini">ENEM 2026</span><div id="timer-enem" class="countdown-mini">00d 00h 00m</div></div>
                    <div class="exam-card-mini mini-fuvest"><span class="exam-name-mini">FUVEST 2027</span><div id="timer-fuvest" class="countdown-mini">00d 00h 00m</div></div>
                    <div class="exam-card-mini mini-unicamp"><span class="exam-name-mini">UNICAMP 2026</span><div id="timer-unicamp" class="countdown-mini">00d 00h 00m</div></div>
                    <div class="exam-card-mini mini-unesp"><span class="exam-name-mini">UNESP 2027</span><div id="timer-unesp" class="countdown-mini">00d 00h 00m</div></div>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="video-grid" id="videoGrid">
                            <?php foreach ($videos as $video): 
                                $vId = $video['id']['videoId'];
                                $vTitle = $video['snippet']['title'];
                                $isFav = in_array($vId, $meus_favs);
                            ?>
                                <div class="video-card" style="background: #f9f9f9; padding: 10px; border-radius: 15px;">
                                    <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0;">
                                        <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 10px;" src="https://www.youtube.com/embed/<?= $vId ?>" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                    <p style="color: #4B0082; font-weight: bold; margin-top: 10px; font-size: 14px; text-align: center;">
                                        <?= htmlspecialchars(mb_strimwidth($vTitle, 0, 45, "...")) ?>
                                        <button class="fav-btn <?= $isFav ? 'star-on' : 'star-off' ?>" onclick="toggleFav('<?= $vId ?>', '<?= addslashes($vTitle) ?>', this)">★</button>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="caixa-favoritos-nova">
                        <div class="video-header">
                            <h2 class="video-title" style="color: #4B0082;">Meus Vídeos Salvos ★</h2>
                        </div>
                        <div class="video-grid" id="listaFavoritos">
                            <?php
                            $res_salvos = $conn->query("SELECT * FROM favoritos WHERE id_usuario = $id");
                            if ($res_salvos->num_rows > 0):
                                while($f = $res_salvos->fetch_assoc()): ?>
                                    <div class="video-card" style="background: #fdfdfd; padding: 10px; border-radius: 15px; border: 1px solid #eee;">
                                        <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0;">
                                            <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 10px;" src="https://www.youtube.com/embed/<?= $f['video_id'] ?>" frameborder="0" allowfullscreen></iframe>
                                        </div>
                                        <p style="color: #4B0082; font-weight: bold; margin-top: 10px; font-size: 14px; text-align: center;">
                                            <?= htmlspecialchars(mb_strimwidth($f['titulo_video'], 0, 45, "...")) ?>
                                            <button class="fav-btn star-on" onclick="toggleFav('<?= $f['video_id'] ?>', '<?= addslashes($f['titulo_video']) ?>', this)">★</button>
                                        </p>
                                    </div>
                                <?php endwhile;
                            else: ?>
                                <p style="grid-column: 1/-1; text-align: center; color: #999; padding: 20px;">Você ainda não favoritou vídeos.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        function toggleFav(vId, vTit, btn) {
            const formData = new FormData();
            formData.append('v_id', vId);
            formData.append('v_tit', vTit);

            fetch('fav.php', { method: 'POST', body: formData })
            .then(response => {
                // Se a resposta for ok, recarrega para atualizar as duas listas
                location.reload();
            })
            .catch(err => console.error("Erro no favorito:", err));
        }

        // Timers mantidos
        function createTimer(id, targetDate) {
            const target = new Date(targetDate).getTime();
            const element = document.getElementById(id);
            if(!element) return;
            setInterval(() => {
                const now = new Date().getTime();
                const diff = target - now;
                if (diff <= 0) { element.innerHTML = "PROVA EM ANDAMENTO"; return; }
                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);
                element.innerHTML = `${d}d ${h.toString().padStart(2, '0')}h ${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
            }, 1000);
        }

        createTimer("timer-enem", "Nov 1, 2026 13:00:00");
        createTimer("timer-fuvest", "Nov 15, 2026 13:00:00");
        createTimer("timer-unicamp", "Oct 18, 2026 13:00:00");
        createTimer("timer-unesp", "Nov 15, 2026 13:00:00");
    </script>
</body>
</html>