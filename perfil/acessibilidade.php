<?php
session_start();

// 1. Verifica se a variável de sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

$id_usuario = $_SESSION['id'];

// 2. Verifica se o ID da sessão é válido e busca a foto e permissão (tipo/nivel) do usuário
$stmtUser = $conn->prepare("SELECT nome, email, foto, tipo FROM usuario WHERE id_usuario = ?");
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$user = $resUser->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// 3. LÓGICA DE PROCESSAMENTO DO CORTE DA IMAGEM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'crop_image') {
    ob_clean(); 
    header('Content-Type: application/json');

    try {
        $diretorioFisico = "../assets/img/perfis/";
        $diretorioBanco = "assets/img/perfis/";
        
        if (!file_exists($diretorioFisico)) { 
            mkdir($diretorioFisico, 0777, true); 
        }

        $base64_image = $_POST['image_data']; 
        $data = explode(',', $base64_image);
        $image_content = base64_decode($data[1]);

        $nomeArquivo = "user_" . $id_usuario . "_" . time() . ".webp";
        $caminhoCompletoFisico = $diretorioFisico . $nomeArquivo;
        $caminhoBanco = $diretorioBanco . $nomeArquivo;

        if (file_put_contents($caminhoCompletoFisico, $image_content)) {
            $busca = $conn->prepare("SELECT foto FROM usuario WHERE id_usuario = ?");
            $busca->bind_param("i", $id_usuario);
            $busca->execute();
            $fotoAntiga = $busca->get_result()->fetch_assoc()['foto'] ?? null;

            if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp') {
                $caminhoArquivoAntigo = "../" . $fotoAntiga;
                if (file_exists($caminhoArquivoAntigo)) { 
                    @unlink($caminhoArquivoAntigo); 
                }
            }

            $upd = $conn->prepare("UPDATE usuario SET foto = ? WHERE id_usuario = ?");
            $upd->bind_param("si", $caminhoBanco, $id_usuario);
            $upd->execute();

            echo json_encode(['status' => 'success', 'redirect' => 'configperfil.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Não foi possível escrever o arquivo na pasta perfis.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

$caminhoPadrao = "../assets/img/default-user.webp"; 
$fotoExibicao = (!empty($user['foto']) && file_exists("../" . $user['foto'])) ? "../" . $user['foto'] : $caminhoPadrao;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessibilidade - Phronesis</title>
    <?php
        // Versiona CSS/JS pela data de modificação, evitando que o navegador use arquivo antigo em cache
        $verCss = @filemtime(__DIR__ . '/../assets/css/acessibilidade.css') ?: time();
        $verJs  = @filemtime(__DIR__ . '/../assets/js/lib/acessibilidade.js') ?: time();
    ?>
    <link rel="stylesheet" href="../assets/css/acessibilidade.css?v=<?php echo $verCss; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.webp">
    
    <script src="../assets/js/lib/acessibilidade.js?v=<?php echo $verJs; ?>"></script>
</head>
<body>

    <div class="main-wrapper">

        <!-- Header topo (Celular e Tablet) -->
        <header class="mobile-header">
            <div class="mobile-logo-container">
                <a href="../index.php"><img src="../assets/img/logos/logoSite.webp" alt="Phronesis" class="logo-light"></a>
                <a href="../index.php"><img src="../assets/img/logos/logoescuro.webp" alt="Phronesis" class="logo-dark"></a>
            </div>
            <button type="button" class="mobile-menu-toggle" id="btnOpenSidebar"
                    aria-label="Abrir menu" aria-expanded="false" aria-controls="sidebar">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </header>

        <!-- Fundo escurecido (Overlay) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar / Menu Drawer -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <a href="../index.php"><img src="../assets/img/logos/logoSite.webp" alt="Phronesis" class="logo-light"></a>
                    <a href="../index.php"><img src="../assets/img/logos/logoescuro.webp" alt="Phronesis" class="logo-dark"></a>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-title">Conta Pessoal</p>
                <ul>
                    <li>
                        <a href="configperfil.php">
                            <span class="nav-icon">👤</span>
                            <span class="nav-text">Meu perfil</span>
                        </a>
                    </li>
                    <li>
                        <a href="acessibilidade.php" class="nav-link-acessibilidade active">
                            <img src="../assets/img/acessibilidades/acessibilidade.webp" alt="Acessibilidade" class="icon-acessibilidade" onerror="this.outerHTML='<span>♿</span>'">
                            <span class="nav-text">Acessibilidade</span>
                        </a>
                    </li>
                </ul>

                            <nav class="sidebar-nav">
                <!-- Navegação entre páginas (só PC) -->
                <div class="nav-desktop">
                    <p class="nav-title">Navegação</p>
                    <ul>
                        <li>
                            <a href="../index.php">
                                <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
                                <span class="nav-text">Início</span>
                            </a>
                        </li>
                        <li>
                            <a href="../pages/livros.php">
                                <span class="nav-icon"><i class="fa-solid fa-book"></i></span>
                                <span class="nav-text">Biblioteca</span>
                            </a>
                        </li>
                        <li>
                            <a href="../pages/material.php">
                                <span class="nav-icon"><i class="fa-solid fa-tv"></i></span>
                                <span class="nav-text">Material</span>
                            </a>
                        </li>
                        <li>
                            <a href="../pages/cronograma.php">
                                <span class="nav-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <span class="nav-text">Cronogramas</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if (isset($user['tipo']) && ($user['tipo'] === 'admin' || $user['tipo'] === '1' || $user['tipo'] === 1)): ?>
                    <p class="nav-title">ADMINISTRAÇÃO</p>
                    <ul>
                        <li>
                            <a href="../pages/admin_materias.php">
                                <span class="nav-icon">🛠️</span>
                                <span class="nav-text">Painel ADM</span>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="content">
            <header class="profile-banner">
                <img src="../assets/img/banners/acessibilidadeClaro.webp" alt="Banner" class="banner-img banner-light">
                <img src="../assets/img/banners/acessibilidadeEscuro.webp" alt="Banner" class="banner-img banner-dark">
            </header>

            <div class="container-acessibilidade">
                <section class="theme-section">
                    <h2>Tema:</h2>
                    <p class="subtitle">Escolha como você gostaria de visualizar o Phronesis. Selecione um tema ou sincronize temas com as preferências do seu sistema.</p>
                    
                    <div class="theme-cards">
                        <div class="theme-card" id="btn-light-theme">
                            <h3>Claro</h3>
                            <p>Ideal para: Ambientes muito iluminados ou sob luz solar direta.</p>
                            <div class="preview-box">
                                <img src="../assets/img/acessibilidades/modoclaro.webp" alt="Prévia Modo Claro">
                            </div>
                        </div>

                        <div class="theme-card" id="btn-dark-theme">
                            <h3>Escuro</h3>
                            <p>Ambientes com pouca luz ou períodos noturnos.</p>
                            <div class="preview-box">
                                <img src="../assets/img/acessibilidades/modoescuro.webp" alt="Prévia Modo Escuro">
                            </div>
                        </div>
                    </div>
                </section>

                <section class="font-section">
                    <h2>Tamanho do texto</h2>
                    <p class="subtitle">Tamanho da fonte será alterado conforme você ajustar o controle deslizante.</p>
                    
                    <div class="font-slider-container">
                        <span class="lbl-small">A</span>
                        <div class="slider-wrapper">
                            <input type="range" id="font-slider" min="1" max="3" value="1" step="1">
                            <div class="slider-ticks">
                                <span class="tick" onclick="setSliderValue(1)"></span>
                                <span class="tick" onclick="setSliderValue(2)"></span>
                                <span class="tick" onclick="setSliderValue(3)"></span>
                            </div>
                        </div>
                        <span class="lbl-large">A</span>
                    </div>
                </section>

                <section class="libras-section">
                    <div class="libras-info">
                        <h2>Acesse o conhecimento em Libras de forma rápida e intuitiva.</h2>
                        <p>Integra a tradução em sinais à sua navegação, garantindo acesso pleno ao acervo da biblioteca.</p>
                    </div>
                    <div class="libras-controls">
                        <img src="../assets/img/acessibilidades/acessibilidade.webp" alt="Ícone de Libras" class="libras-icon">
                        <label class="switch">
                            <input type="checkbox" id="toggle-libras">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </section>
            </div>
        </main>
    </div>

        <!-- ================= NAVEGAÇÃO INFERIOR (só celular) ================= -->
    <nav class="bottom-nav" aria-label="Navegação principal">
        <a href="../index.php" class="bn-item">
            <i class="fa-solid fa-house"></i><span>Início</span>
        </a>
        <a href="../pages/livros.php" class="bn-item">
            <i class="fa-solid fa-book"></i><span>Biblioteca</span>
        </a>
        <a href="../pages/material.php" class="bn-item">
            <i class="fa-solid fa-tv"></i><span>Material</span>
        </a>
        <a href="../pages/cronograma.php" class="bn-item">
            <i class="fa-regular fa-calendar-days"></i><span>Cronogramas</span>
        </a>
        <a href="configperfil.php" class="bn-item ativo" aria-current="page">
            <i class="fa-regular fa-user"></i><span>Perfil</span>
        </a>
    </nav>

    <?php include 'vlibras.php'; ?>
</body>
</html>