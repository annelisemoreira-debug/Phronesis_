<?php
// =========================================================================
// interface/header.php — Cabeçalho do site (incluído em todas as páginas)
// =========================================================================
include __DIR__ . '/../conexao.php';

// -------------------------------------------------------------------------
// 1. CAMINHO BASE: páginas dentro de pages/ ou perfil/ precisam subir um nível
// -------------------------------------------------------------------------
$script_name = $_SERVER['SCRIPT_NAME'];

if (strpos($script_name, '/pages/') !== false || strpos($script_name, '/perfil/') !== false) {
    $path = "../";
} else {
    $path = "";
}

// No index o menu hambúrguer não tem função (a navegação fica na barra inferior)
// Páginas que usam a barra inferior e não precisam do hambúrguer
$paginasSemHamburger = ['index.php', 'livros.php', 'cronograma.php'];
$semHamburger = in_array(basename($script_name), $paginasSemHamburger, true);

// -------------------------------------------------------------------------
// 2. DADOS DO USUÁRIO LOGADO (foto de perfil e tipo de conta)
// -------------------------------------------------------------------------
$fotoPerfil  = $path . "assets/img/default-user.webp";
$usuarioTipo = 'user';

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    $stmt = $conn->prepare("SELECT foto, tipo FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $usuarioTipo = $res['tipo'];

        if (!empty($res['foto'])) {
            if (filter_var($res['foto'], FILTER_VALIDATE_URL)) {
                // Foto vinda do Google (URL completa)
                $fotoPerfil = $res['foto'];
            } else {
                // Foto enviada pelo usuário (caminho local)
                $fotoLimpa  = ltrim($res['foto'], '/');
                $fotoPerfil = $path . $fotoLimpa;
            }
        }
    }
}
?>

<header class="<?= $semHamburger ? 'com-bottom-nav' : '' ?>">

    <!-- ================= LOGO (4 variações: PC/celular × claro/escuro) ================= -->
    <a href="<?= $path ?>index.php" class="logoMarca">
        <!-- Versões do PC -->
        <img src="<?= $path ?>assets/img/logos/logoSite.webp" class="logo-desktop logo-light" alt="Phronesis">
        <img src="<?= $path ?>assets/img/logos/logoescuro.webp" class="logo-desktop logo-dark" alt="Phronesis">

        <!-- Versões do celular -->
        <img src="<?= $path ?>assets/img/logos/logoCelularClaro.webp" class="logo-mobile logo-light" alt="Phronesis">
        <img src="<?= $path ?>assets/img/logos/logoCelularEscuro.webp" class="logo-mobile logo-dark" alt="Phronesis">
    </a>

    <!-- ================= MENU PRINCIPAL ================= -->
    <nav class="menuNav" id="menuNav">
        <ul class="menu">
            <li class="materiais"><a href="<?= $path ?>pages/material.php">Materiais</a></li>
            <li class="livros"><a href="<?= $path ?>pages/livros.php">Obras Obrigatórias</a></li>
            <li class="cronogramas"><a href="<?= $path ?>pages/cronograma.php">Cronogramas</a></li>
        </ul>
    </nav>

    <!-- ================= ÁREA DIREITA (hambúrguer + painel + perfil) ================= -->
    <div class="perfil-wrapper">
        <?php if (isset($_SESSION['id'])): ?>

            <!-- Admin vê o link "ADM"; estudante vê o texto "PAINEL DO ESTUDANTE" -->
            <?php if ($usuarioTipo === 'admin'): ?>
                <a href="<?= $path ?>pages/admin_materias.php" class="botao-nav-adm">ADM</a>
            <?php else: ?>
                <span class="texto-painel">PAINEL DO ESTUDANTE</span>
            <?php endif; ?>

            <!-- Botão hambúrguer (celular) -->
            <?php if (!$semHamburger): ?>
                <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            <?php endif; ?>

            <!-- Foto de perfil + menu suspenso -->
            <div class="perfil-container" id="perfilBtn">
                <img src="<?= htmlspecialchars($fotoPerfil) ?>"
                     class="foto-perfil"
                     alt="Perfil"
                     referrerpolicy="no-referrer"
                     onerror="this.onerror=null; this.src='<?= $path ?>assets/img/default-user.webp';">

                <div class="menu-dropdown" id="menuDropdown">
                    <a href="<?= $path ?>perfil/configperfil.php">Meu Perfil</a>
                    <?php if ($usuarioTipo === 'admin'): ?>
                        <a href="<?= $path ?>pages/admin_materias.php">Painel ADM</a>
                    <?php endif; ?>
                    <a href="<?= $path ?>logout.php" style="color: red;">Sair</a>
                </div>
            </div>

        <?php else: ?>

            <!-- VISITANTE (não logado) -->
            <?php if (!$semHamburger): ?>
                <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            <?php endif; ?>

            <div class="menu">
                <div class="entrar"><a href="<?= $path ?>login.php">Entrar</a></div>
            </div>

        <?php endif; ?>
    </div>
</header>

<!-- ================= COMPORTAMENTO DO HEADER ================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const hamburgerBtn    = document.getElementById("hamburgerBtn");
    const menuNav         = document.getElementById("menuNav");
    const perfilContainer = document.querySelector(".perfil-container");
    const dropdown        = document.getElementById("menuDropdown");

    // Abre/fecha o menu hambúrguer (celular)
    if (hamburgerBtn && menuNav) {
        hamburgerBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            menuNav.classList.toggle("open");
            hamburgerBtn.classList.toggle("active");
        });
    }

    // Abre/fecha o menu suspenso do perfil
    if (perfilContainer && dropdown) {
        perfilContainer.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdown.classList.toggle("show");
        });
    }

    // Clicar fora fecha tudo
    document.addEventListener("click", function () {
        if (dropdown) dropdown.classList.remove("show");
        if (menuNav) menuNav.classList.remove("open");
        if (hamburgerBtn) hamburgerBtn.classList.remove("active");
    });
});
</script>