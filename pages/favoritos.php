<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';
require_once __DIR__ . '/_video_card.php';

$id = (int) $_SESSION['id'];

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

// Vídeos salvos (mais recentes primeiro)
$stmt = $conn->prepare("SELECT video_id, titulo_video FROM favoritos WHERE id_usuario = ? ORDER BY id_favorito DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$salvos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Vistos
$stmt = $conn->prepare("SELECT video_id FROM vistos WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$meus_vistos = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'video_id');
$stmt->close();

$materiaAtiva = null;
$paginaAtual = 'favoritos';

// Versiona o CSS dos banners pela data de modificação (evita cache antigo)
$verBanner = @filemtime(__DIR__ . '/../assets/css/banner.css') ?: time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vídeos Salvos - Phronesis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/material.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/banner.css?v=<?= $verBanner ?>">
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
<body data-pagina="favoritos">
    <?php include '../interface/header.php'; ?>

    <!-- ================= BANNER (MODO CLARO E ESCURO · PC / TABLET / CELULAR) ================= -->
    <section class="banner">
        <picture class="banner-light">
            <source media="(max-width: 600px)"  srcset="../assets/img/banners/cellphone/favoritosCelularClaro.webp">
            <source media="(max-width: 1024px)" srcset="../assets/img/banners/tablet/favoritosTabletClaro.webp">
            <img src="../assets/img/banners/pc/favoritosClaro.webp" alt="Banner Materiais">
        </picture>
        <picture class="banner-dark">
            <source media="(max-width: 600px)"  srcset="../assets/img/banners/cellphone/favoritosCelularEscuro.webp">
            <source media="(max-width: 1024px)" srcset="../assets/img/banners/tablet/favoritosTabletEscuro.webp">
            <img src="../assets/img/banners/pc/favoritosEscuro.webp" alt="Banner Materiais Escuro">
        </picture>
    </section>

    <div class="wrapper">
        <?php include __DIR__ . '/_material_sidebar.php'; ?>

        <div class="principalContent">
            <main class="areaContent">
                <div class="video-header">
                    <h1 class="video-title titulo-salvos">Meus Vídeos Salvos <i class="fa-solid fa-star"></i></h1>
                    <a href="material.php" class="link-voltar"><i class="fa-solid fa-arrow-left"></i> Voltar aos materiais</a>
                </div>

                <div class="video-grid" id="listaFavoritos" data-lista>
                    <?php foreach ($salvos as $f):
                        renderVideoCard($f['video_id'], $f['titulo_video'], true, in_array($f['video_id'], $meus_vistos, true));
                    endforeach; ?>
                </div>

                <p class="sem-favoritos" id="semFavoritos" <?= empty($salvos) ? '' : 'hidden' ?>>
                    Você ainda não salvou vídeos. Toque na estrela de um vídeo em Materiais para salvá-lo aqui.
                </p>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/_bottom_nav.php'; ?>

    <script src="../assets/js/lib/material.js" defer></script>
    <?php include '../perfil/vlibras.php'; ?>
    <?php include '../interface/footer.php';?>
</body>
</html>