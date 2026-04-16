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
$termoBusca = 'aula biologia vestibular'; // Padrão inicial

if (isset($_GET['search']) && !empty($_GET['search'])) {
    // Se o usuário usou a barra de pesquisa
    $termoBusca = $_GET['search'] . ' aula vestibular';
} elseif (isset($_GET['materia'])) {
    // Se o usuário clicou em uma matéria no menu lateral
    $termoBusca = 'aula ' . $_GET['materia'] . ' vestibular';
}

try {
    // 5. Chamada oficial da API do YouTube
    $res = $youtube->search->listSearch('snippet', [
        'q'             => $termoBusca, 
        'maxResults'    => 18,           // Mostra 12 vídeos para preencher bem o grid
        'type'          => 'video',
        'videoDuration' => 'medium',    // Filtra vídeos de 4 a 20 minutos
        'relevanceLanguage' => 'pt'
    ]);
    $videos = $res['items'];
} catch (Exception $e) {
    $erro_api = $e->getMessage();
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
                            <?php if (isset($videos) && !empty($videos)): ?>
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
                            <?php elseif (isset($erro_api)): ?>
                                <p style="color: red;">Erro na API: <?= htmlspecialchars($erro_api) ?></p>
                            <?php else: ?>
                                <p>Nenhum vídeo encontrado para "<?= htmlspecialchars($termoBusca) ?>".</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <aside class="sidebar-complementar">
                        </aside>

                </section>
            </main>
        </div>
    </div>
</body>
</html>