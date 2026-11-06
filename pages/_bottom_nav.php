<?php
// pages/_bottom_nav.php — espera $paginaAtual
// Valores: 'inicio' | 'livros' | 'material' | 'favoritos' | 'cronograma' | 'perfil'
$paginaAtual = $paginaAtual ?? '';

$itensNav = [
    ['../index.php',                 'fa-solid fa-house',            'Início',      ['inicio']],
    ['livros.php',                   'fa-solid fa-book',             'Biblioteca',  ['livros']],
    ['material.php',                 'fa-solid fa-tv',               'Material',    ['material', 'favoritos']],
    ['cronograma.php',               'fa-regular fa-calendar-days',  'Cronogramas', ['cronograma']],
    ['../perfil/configperfil.php',   'fa-regular fa-user',           'Perfil',      ['perfil']],
];
?>
<nav class="bottom-nav" aria-label="Navegação principal">
    <?php foreach ($itensNav as [$href, $icone, $rotulo, $paginas]):
        $ativo = in_array($paginaAtual, $paginas, true); ?>
        <a href="<?= $href ?>" class="bn-item<?= $ativo ? ' ativo' : '' ?>"<?= $ativo ? ' aria-current="page"' : '' ?>>
            <i class="<?= $icone ?>"></i><span><?= $rotulo ?></span>
        </a>
    <?php endforeach; ?>
</nav>