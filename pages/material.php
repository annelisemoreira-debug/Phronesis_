<?php
// 1. Configurações de erro para desenvolvimento
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Importação do Google Client (Autoload)
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Configuração da API Key
$minha_chave = 'AIzaSyCENf31FjtasIsEqd20BTK53vrD_v3GuxQ';
$client = new Google\Client();
$client->setDeveloperKey($minha_chave);
$youtube = new Google\Service\YouTube($client);

// 4. Lógica de Busca Dinâmica
$termoBuscaOriginal = isset($_GET['search']) ? $_GET['search'] : (isset($_GET['materia']) ? $_GET['materia'] : 'Biologia');
$termoBuscaAPI = $termoBuscaOriginal . ' aula vestibular';

$videos = []; // Inicializa a variável

try {
    // 5. Chamada oficial da API do YouTube
    $res = $youtube->search->listSearch('snippet', [
        'q'             => $termoBuscaAPI, 
        'maxResults'    => 12,           
        'type'          => 'video',
        'videoDuration' => 'medium',    
        'relevanceLanguage' => 'pt'
    ]);
    $videos = $res['items'];

} catch (Exception $e) {
    // 6. BACKUP: Se a cota estourar (Erro 403), usamos vídeos fixos para não quebrar o site
    // Cada busca economizada aqui poupa 100 unidades de cota
    $videos = [
        [
            'id' => ['videoId' => '0P-fO8P9mSg'],
            'snippet' => [
                'title' => '[MODO ECONÔMICO] Introdução à ' . $termoBuscaOriginal,
                'thumbnails' => ['high' => ['url' => 'https://img.youtube.com/vi/0P-fO8P9mSg/hqdefault.jpg']]
            ]
        ],
        [
            'id' => ['videoId' => '9I86L8uWbYk'],
            'snippet' => [
                'title' => '[MODO ECONÔMICO] Revisão Geral para Vestibular',
                'thumbnails' => ['high' => ['url' => 'https://img.youtube.com/vi/9I86L8uWbYk/hqdefault.jpg']]
            ]
        ],
        [
            'id' => ['videoId' => 'dQw4w9WgXcQ'],
            'snippet' => [
                'title' => '[MODO ECONÔMICO] Dicas Extras de Estudo',
                'thumbnails' => ['high' => ['url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg']]
            ]
        ]
    ];
    // Opcional: log do erro para o desenvolvedor ver no console, sem assustar o usuário
    error_log("YouTube API Quota Exceeded. Using Mock Data.");
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
            </nav>
        </aside>
    
        <div class="principalContent">
            <header>
                <nav class="menuNav">
                    <ul class="menu">
                        <li class="livros"><a href="livros.php">Obras Obrigatórias</a></li>
                        <li class="home"><a href="../index.php">Início</a></li>
                        <li class="cronogramas"><a href="meu_calendario.php">Cronogramas</a></li>
                        <li class="entrar"><a href="../login.php">Entrar</a></li>
                    </ul>
                </nav>
            </header>

            <main class="areaContent">
                <section class="banner">
                    <div></div>
                </section>

                <section class="container-materiais">
                    <div class="video-container-white">
                        <div class="video-header">
                            <h2 class="video-title">Materiais Complementares</h2>
                            
                            <div class="search-box">
                                <form action="" method="GET" style="display: flex; width: 100%; align-items: center;">
                                    <input type="text" 
                                           name="search" 
                                           placeholder="Pesquisar vídeo..." 
                                           id="searchInput" 
                                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    
                                    <button type="submit" style="background: none; border: none; cursor: pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="video-grid" id="videoGrid">
                            <?php if (!empty($videos)): ?>
                                <?php foreach ($videos as $video): ?>
                                    <div class="video-card">
                                        <a href="https://www.youtube.com/watch?v=<?= $video['id']['videoId'] ?>" target="_blank" style="text-decoration:none;">
                                            <img src="<?= $video['snippet']['thumbnails']['high']['url'] ?>" alt="Capa do vídeo" style="width:100%; border-radius:12px;">
                                            <p style="color: #4B0082; font-weight: bold; margin-top: 10px; font-size: 14px;">
                                                <?= mb_strimwidth($video['snippet']['title'], 0, 45, "...") ?>
                                            </p>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Nenhum vídeo disponível no momento.</p>
                            <?php endif; ?>
                        </div>
                    </div>

    <aside class="sidebar-complementar">

                                <div class="exam-widget">

                                    <h3 class="widget-title">Contagem Regressiva</h3>

                                

                                    <div class="exam-grid">

                                        <div class="exam-card enem">

                                            <div class="exam-info">

                                                <span class="exam-name">ENEM 2026</span>

                                                <span class="exam-status">Nacional</span>

                                            </div>

                                            <div id="timer-enem" class="countdown-clock">00d 00h 00m</div>

                                            <div class="progress-bar"><div class="progress-fill" style="width: 60%;"></div></div>

                                        </div>



                                        <div class="exam-card fuvest">

                                            <div class="exam-info">

                                                <span class="exam-name">FUVEST 2026</span>

                                                <span class="exam-status">USP</span>

                                            </div>

                                            <div id="timer-fuvest" class="countdown-clock">00d 00h 00m</div>

                                            <div class="progress-bar"><div class="progress-fill" style="width: 45%;"></div></div>

                                        </div>



                                        <div class="exam-card unicamp">

                                            <div class="exam-info">

                                                <span class="exam-name">UNICAMP 2026</span>

                                                <span class="exam-status">Unicamp</span>

                                            </div>

                                            <div id="timer-unicamp" class="countdown-clock">00d 00h 00m</div>

                                            <div class="progress-bar"><div class="progress-fill" style="width: 75%;"></div></div>

                                        </div>



                                        <div class="exam-card unesp">

                                            <div class="exam-info">

                                                <span class="exam-name">UNESP 2026</span>

                                                <span class="exam-status">Vunesp</span>

                                            </div>

                                            <div id="timer-unesp" class="countdown-clock">00d 00h 00m</div>

                                            <div class="progress-bar"><div class="progress-fill" style="width: 35%;"></div></div>

                                        </div>



                                        <div class="exam-card uerj">

                                            <div class="exam-info">

                                                <span class="exam-name">UERJ 2027</span>

                                                <span class="exam-status">1º EQ</span>

                                            </div>

                                            <div id="timer-uerj" class="countdown-clock">00d 00h 00m</div>

                                            <div class="progress-bar"><div class="progress-fill" style="width: 90%;"></div></div>

                                        </div>



                                        <div class="exam-card ita">

                                            <div class="exam-info">

                                                <span class="exam-name">ITA 2027</span>

                                                <span class="exam-status">Militar</span>

                                            </div>

                                            <div id="timer-ita" class="countdown-clock">00d 00h 00m</div>

                                            <div class="progress-bar"><div class="progress-fill" style="width: 20%;"></div></div>

                                        </div>

                                    </div>

                                </div>



                            <script>

                                function createTimer(id, targetDate) {

                                    const target = new Date(targetDate).getTime();

                                    const element = document.getElementById(id);



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



                                // Chamadas das Funções com Datas Reais (ou estimadas conforme ciclo 2026/2027)

                                createTimer("timer-enem", "Nov 1, 2026 13:00:00");

                                createTimer("timer-fuvest", "Nov 15, 2026 13:00:00");

                                createTimer("timer-unicamp", "Oct 18, 2026 13:00:00");

                                createTimer("timer-unesp", "Nov 15, 2026 13:00:00"); // Data prevista

                                createTimer("timer-uerj", "Jun 7, 2026 09:00:00"); // 1º EQ

                                createTimer("timer-ita", "Oct 13, 2026 08:00:00"); // Estimativa 1ª Fase

                            </script>

                        </aside>



                </section>

            </main>

        </div>

    </div>

    <script>
        document.querySelectorAll('.menuSidebar nav a').forEach(link => {
            link.addEventListener('click', function() {
                // Remove de outros (caso o clique falhe ou demore)
                document.querySelectorAll('.menuSidebar nav a').forEach(l => l.classList.remove('loading'));
                // Adiciona a animação no link clicado
                this.classList.add('loading');
            });
        });
    </script>

</body>
</html>