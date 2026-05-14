<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONEXÃO E BUSCA DO USUÁRIO ---
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "phronesis");
$dadosUser = null;
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id");
    $dadosUser = $res->fetch_assoc();
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
</head>
<body>
    <div class="wrapper">
        <aside class="menuSidebar">
            <div class="logoMarca">
                <a href="../index.php">
                    <img src="../assets/img/logoSidebar.webp" alt="Logo oficial da Phronesis">
                </a>
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
                    <div class="exam-card-mini mini-enem">
                        <span class="exam-name-mini">ENEM 2026</span>
                        <div id="timer-enem" class="countdown-mini">00d 00h 00m</div>
                    </div>
                    <div class="exam-card-mini mini-fuvest">
                        <span class="exam-name-mini">FUVEST 2026</span>
                        <div id="timer-fuvest" class="countdown-mini">00d 00h 00m</div>
                    </div>
                    <div class="exam-card-mini mini-unicamp">
                        <span class="exam-name-mini">UNICAMP 2026</span>
                        <div id="timer-unicamp" class="countdown-mini">00d 00h 00m</div>
                    </div>
                    <div class="exam-card-mini mini-unesp">
                        <span class="exam-name-mini">UNESP 2026</span>
                        <div id="timer-unesp" class="countdown-mini">00d 00h 00m</div>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="principalContent">
            <header>
                <nav class="menuNav">
                    <ul class="menu">
                        <li class="livros"><a href="livros.php">Obras Obrigatórias</a></li>
                        <li class="home"><a href="../index.php">Início</a></li>
                        <li class="cronogramas"><a href="meu_calendario.php">Cronogramas</a></li>
                        <li class="entrar">
    <div class="entrar-container">
        <?php if ($dadosUser): ?>
            <a href="../perfil/configperfil.php" style="display: flex; align-items: center; justify-content: center;">
                <?php 
                    // Verifica se existe foto, se não, usa a padrão .webp
                    $fotoPerfil = !empty($dadosUser['foto']) ? $dadosUser['foto'] : 'assets/img/default-user.webp';
                ?>
                <img src="../<?= $fotoPerfil ?>" class="user-profile-top" alt="Foto de Perfil" onerror="this.src='../assets/img/default-user.webp';">
            </a>
        <?php else: ?>
            <a href="../login.php">Entrar</a>
        <?php endif; ?>
    </div>
</li>
                    </ul>
                </nav>
            </header>

            <main class="areaContent">
                <section class="banner"><div></div></section>

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
                            <?php foreach ($videos as $video): ?>
                                <div class="video-card" style="background: #f9f9f9; padding: 10px; border-radius: 15px;">
                                    <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0;">
                                        <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 10px;" src="https://www.youtube.com/embed/<?= $video['id']['videoId'] ?>" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                    <p style="color: #4B0082; font-weight: bold; margin-top: 10px; font-size: 14px; text-align: center;">
                                        <?= htmlspecialchars(mb_strimwidth($video['snippet']['title'], 0, 50, "...")) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        function createTimer(id, targetDate) {
            const target = new Date(targetDate).getTime();
            const element = document.getElementById(id);
            if(!element) return;

            const interval = setInterval(() => {
                const now = new Date().getTime();
                const diff = target - now;

                if (diff <= 0) {
                    element.innerHTML = "PROVA EM ANDAMENTO";
                    clearInterval(interval);
                    return;
                }

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

        document.querySelectorAll('.menuSidebar nav a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.menuSidebar nav a').forEach(l => l.classList.remove('loading'));
                this.classList.add('loading');
            });
        });
    </script>
</body>
</html>