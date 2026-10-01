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
    <!-- LOGO (Coluna Esquerda) -->
    <a href="../index.php" class="logoMarca">
        <img src="../assets/img/logoComEscrita.png" class="logo-desktop logo-light" alt="Phronesis">
        <img src="../assets/img/logoComEscritaEscura.png" class="logo-desktop logo-dark" alt="Phronesis">
        <img src="../assets/img/logoSemEscrita.png" class="logo-mobile logo-light" alt="Phronesis">
        <img src="../assets/img/logoSemEscritaEscura.png" class="logo-mobile logo-dark" alt="Phronesis">
    </a>

    <!-- MENU PRINCIPAL (Desktop e Mobile) -->
    <nav class="menuNav" id="menuNav">
        <ul class="menu">
            <li class="materiais"><a href="../pages/material.php">Materiais</a></li>
            <li class="livros"><a href="../pages/obras.php">Obras Obrigatórias</a></li>
            <li class="cronogramas"><a href="../pages/cronograma.php">Cronogramas</a></li>
        </ul>
    </nav>

    <!-- ÁREA DIREITA (Hambúrguer + Foto de Perfil) -->
    <div class="perfil-wrapper">
        <span class="texto-painel">PAINEL DO ESTUDANTE</span>

        <!-- Botão Hambúrguer -->
        <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

<!-- CONTAINER DO PERFIL (Segundo) -->
        <div class="perfil-container" onclick="toggleDropdown()">
            <img src="<?= !empty($dadosUser['foto']) ? '../' . $dadosUser['foto'] : '../assets/img/perfil-padrao.png' ?>" class="foto-perfil" alt="Perfil">
            
            <div class="menu-dropdown" id="menuDropdown">
                <a href="../perfil/configperfil.php">Meu Perfil</a>
                <a href="../logout.php">Sair</a>
            </div>
        </div>
    </div>
</header>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        const perfilBtn = document.getElementById("perfilBtn");
        const dropdownPerfil = document.getElementById("dropdownPerfil");
        const hamburgerBtn = document.getElementById("hamburgerBtn");
        const menuNav = document.getElementById("menuNav");

        // Menu hambúrguer no mobile
        if (hamburgerBtn && menuNav) {
            hamburgerBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                menuNav.classList.toggle("open");
                hamburgerBtn.classList.toggle("active");
            });
        }

        // Dropdown do perfil
        if (perfilBtn && dropdownPerfil) {
            perfilBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                dropdownPerfil.classList.toggle("show");
            });
        }

        // Fechar menus ao clicar fora
        document.addEventListener("click", function(e) {
            if (perfilBtn && !perfilBtn.contains(e.target) && dropdownPerfil) {
                dropdownPerfil.classList.remove("show");
            }
            if (menuNav && !menuNav.contains(e.target) && hamburgerBtn && !hamburgerBtn.contains(e.target)) {
                menuNav.classList.remove("open");
                hamburgerBtn.classList.remove("active");
            }
        });
    });

// Alterna a exibição do Menu Hambúrguer no Mobile
function toggleMobileMenu() {
    const menuNav = document.getElementById('menuNav');
    const hamburgerBtn = document.getElementById('hamburgerBtn');

    if (menuNav) {
        menuNav.classList.toggle('open');
    }
    if (hamburgerBtn) {
        hamburgerBtn.classList.toggle('active');
    }
}

// Alterna a exibição do Dropdown do Perfil
function toggleDropdown() {
    const dropdown = document.getElementById('menuDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Fecha menus ao clicar fora deles
window.addEventListener('click', function(e) {
    const perfilContainer = document.querySelector('.perfil-container');
    const dropdown = document.getElementById('menuDropdown');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const menuNav = document.getElementById('menuNav');

    // Fechar Dropdown do perfil se clicar fora
    if (dropdown && perfilContainer && !perfilContainer.contains(e.target)) {
        dropdown.classList.remove('show');
    }

    // Fechar Menu Hambúrguer se clicar fora dele e do menu
    if (menuNav && hamburgerBtn && !hamburgerBtn.contains(e.target) && !menuNav.contains(e.target)) {
        menuNav.classList.remove('open');
        hamburgerBtn.classList.remove('active');
    }
});

</script>