<?php
session_start();

// 1. Verifica se a variável de sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

$id_usuario = $_SESSION['id'];

// Verifica se o ID da sessão ainda é válido no banco de dados
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id_usuario");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// 3. LÓGICA DE PROCESSAMENTO DO CORTE
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
            $fotoAntiga = $busca->get_result()->fetch_assoc()['foto'];

            if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp') {
                $caminhoArquivoAntigo = "../" . $fotoAntiga;
                if (file_exists($caminhoArquivoAntigo)) { @unlink($caminhoArquivoAntigo); }
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

// 4. BUSCA DADOS PARA EXIBIÇÃO
$stmt = $conn->prepare("SELECT nome, email, foto FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$caminhoPadrao = "../assets/img/default-user.webp"; 
$fotoExibicao = (!empty($user['foto']) && file_exists("../" . $user['foto'])) ? "../" . $user['foto'] : $caminhoPadrao;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessibilidade - Phronesis</title>
    <link rel="stylesheet" href="../assets/css/acessibilidade.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.png">
    
    <script>
        (function () {
            const savedTheme = localStorage.getItem('site-theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
</head>
<body>

    <div class="main-wrapper">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/img/logos/logoSite.webp" alt="Phronesis" class="logo-light">
                <img src="../assets/img/logos/logoescuro.webp" alt="Phronesis" class="logo-dark">
            </div>
            <nav>
                <p class="nav-title">Conta Pessoal</p>
                <ul>
                    <li><a href="configperfil.php"><span>👤</span> Meu perfil</a></li>
                    <li>
                        <a href="acessibilidade.php" class="nav-link-acessibilidade active">
                            <img src="../assets/img/acessibilidade/acessibilidade.webp" alt="Acessibilidade" class="icon-acessibilidade">
                            <span>Acessibilidade</span>
                        </a>
                    </li>
                </ul>
                <p class="nav-title">Voltar</p>
                <ul>
                    <li><a href="../index.php"><span>🏠</span> Início</a></li>
                    <li><a href="../pages/livros.php"><span>📚</span> Livros</a></li>
                    <li><a href="../pages/material.php"><span>📝</span> Matérias</a></li>
                    <li><a href="../pages/cronograma.php"><span>📅</span> Cronograma</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="profile-banner">
                <img src="../assets/img/banners/acessibilidadeclaro.webp" alt="Banner" class="banner-img banner-light">
                <img src="../assets/img/banners/escuroacessibilidade.webp" alt="Banner" class="banner-img banner-dark">
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

    <?php include 'vlibras.php'; ?>

    <script src="../assets/js/lib/acessibilidade.js" defer></script>
</body>
</html>