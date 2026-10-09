<?php // pages/_bottom_nav.php — espera $paginaAtual ?>
<nav class="bottom-nav" aria-label="Navegação principal">
    <a href="../index.php" class="bn-item">
        <i class="fa-solid fa-house"></i><span>Início</span>
    </a>
    <a href="livros.php" class="bn-item">
        <i class="fa-solid fa-book"></i><span>Biblioteca</span>
    </a>
    <a href="material.php" class="bn-item <?= in_array($paginaAtual, ['material', 'favoritos'], true) ? 'ativo' : '' ?>"
       <?= in_array($paginaAtual, ['material', 'favoritos'], true) ? 'aria-current="page"' : '' ?>>
        <i class="fa-solid fa-tv"></i><span>Material</span>
    </a>
    <a href="cronograma.php" class="bn-item">
        <i class="fa-regular fa-calendar-days"></i><span>Cronogramas</span>
    </a>
    <a href="../perfil/configperfil.php" class="bn-item">
        <i class="fa-regular fa-user"></i><span>Perfil</span>
    </a>
</nav>