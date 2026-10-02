<?php
include __DIR__ . '/../conexao.php';

$script_name = $_SERVER['SCRIPT_NAME'];

if (strpos($script_name, '/pages/') !== false || strpos($script_name, '/perfil/') !== false) {
    $path = "../";
} else {
    $path = "";
}

$fotoPerfil = $path . "assets/img/default-user.webp";
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
                $fotoPerfil = $res['foto'];
            } else {
                $fotoLimpa = ltrim($res['foto'], '/');
                $fotoPerfil = $path . $fotoLimpa;
            }
        }
    }
}
?>

<header>
    <!-- CONTAINER DA LOGO (Com todas as 4 variações) -->
    <a href="<?= $path ?>index.php" class="logoMarca">
        <!-- VERSÕES DESKTOP (PC) -->
        <img src="<?= $path ?>assets/img/logos/logoSite.webp" class="logo-desktop logo-light" alt="Phronesis">
        <img src="<?= $path ?>assets/img/logos/logoescuro.webp" class="logo-desktop logo-dark" alt="Phronesis">

        <!-- VERSÕES MOBILE (CELULAR) -->
        <img src="<?= $path ?>assets/img/logos/logoCelularClaro.webp" class="logo-mobile logo-light" alt="Phronesis">
        <img src="<?= $path ?>assets/img/logos/logoCelularEscuro.webp" class="logo-mobile logo-dark" alt="Phronesis">
    </a>

    <!-- MENU PRINCIPAL -->
    <nav class="menuNav" id="menuNav">
        <ul class="menu">
            <li class="materiais"><a href="<?= $path ?>pages/material.php">Materiais</a></li>
            <li class="livros"><a href="<?= $path ?>pages/livros.php">Obras Obrigatórias</a></li>
            <li class="cronogramas"><a href="<?= $path ?>pages/cronograma.php">Cronogramas</a></li>
            
            <?php if (isset($_SESSION['id']) && $usuarioTipo === 'admin'): ?>
           
            <?php endif; ?>
        </ul>
    </nav>

    <!-- ÁREA DIREITA (Hambúrguer + Painel + Perfil) -->
    <div class="perfil-wrapper">
        <?php if (isset($_SESSION['id'])): ?>
            <?php if ($usuarioTipo === 'admin'): ?>
                <a href="<?= $path ?>pages/admin_materias.php" class="botao-nav-adm">ADM</a>
            <?php else: ?>
                <span class="texto-painel">PAINEL DO ESTUDANTE</span>
            <?php endif; ?>

            <!-- Botão Hambúrguer (Mobile) -->
            <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- CONTAINER DO PERFIL -->
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
            <!-- SE O USUÁRIO NÃO ESTIVER LOGADO -->
            <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="menu">
                <div class="entrar"><a href="<?= $path ?>login.php">Entrar</a></div>
            </div>
        <?php endif; ?>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const hamburgerBtn = document.getElementById("hamburgerBtn");
    const menuNav = document.getElementById("menuNav");
    const perfilContainer = document.querySelector(".perfil-container");
    const dropdown = document.getElementById("menuDropdown");

    // Abre/fecha o menu hambúrguer (mobile)
    if (hamburgerBtn && menuNav) {
        hamburgerBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            menuNav.classList.toggle("open");
            hamburgerBtn.classList.toggle("active");
        });
    }

    // Abre/fecha o dropdown do perfil
    if (perfilContainer && dropdown) {
        perfilContainer.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdown.classList.toggle("show");
        });
    }

    // Fecha tudo ao clicar fora
    document.addEventListener("click", function () {
        if (dropdown) dropdown.classList.remove("show");
        if (menuNav) menuNav.classList.remove("open");
        if (hamburgerBtn) hamburgerBtn.classList.remove("active");
    });
});
</script>