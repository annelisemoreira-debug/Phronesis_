<?php
// pages/_material_sidebar.php
// Espera: $materiaAtiva (string|null) e $paginaAtual ('material' | 'favoritos')

// AJUSTE AQUI: datas das provas (mesmas do livros.php)
$PROVAS = [
    ['rotulo' => 'ENEM 2026',    'data' => '2026-11-08T13:30:00-03:00', 'cor' => 'amarelo'],
    ['rotulo' => 'FUVEST 2027',  'data' => '2026-11-22T13:00:00-03:00', 'cor' => 'vermelho'],
    ['rotulo' => 'UNICAMP 2026', 'data' => '2026-10-25T13:30:00-03:00', 'cor' => 'verde'],
    ['rotulo' => 'UNESP 2027',   'data' => '2026-11-29T14:00:00-03:00', 'cor' => 'roxo'],
];

// parâmetro => [rótulo, ícone, cor]
$MATERIAS = [
    'Linguagens' => ['Linguagens', 'fa-book-open',     '#8654f3'],
    'Matematica' => ['Matemática', 'fa-square-root-variable', '#f5b100'],
    'Fisica'     => ['Física',     'fa-atom',          '#3e93c9'],
    'Biologia'   => ['Biologia',   'fa-dna',           '#1db97a'],
    'Quimica'    => ['Química',    'fa-flask',         '#e5604f'],
    'Historia'   => ['História',   'fa-landmark',      '#f08a24'],
    'Geografia'  => ['Geografia',  'fa-earth-americas','#17a2b8'],
];
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <button type="button" class="sidebar-close" id="btnFecharSidebar" aria-label="Fechar menu">&times;</button>

    <section class="side-bloco">
        <h2 class="side-titulo">Explore por matéria:</h2>
        <ul class="filtro-materia">
            <?php foreach ($MATERIAS as $param => [$rotulo, $icone, $cor]): ?>
                <li>
                    <a href="material.php?materia=<?= e($param) ?>"
                       class="materia-link <?= ($paginaAtual === 'material' && $materiaAtiva === $param) ? 'ativo' : '' ?>">
                        <i class="fa-solid <?= e($icone) ?>" style="color: <?= e($cor) ?>"></i><?= e($rotulo) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="side-bloco">
        <h2 class="side-titulo">Meus vídeos</h2>
        <ul class="filtro-materia">
            <li>
                <a href="favoritos.php" class="materia-link <?= $paginaAtual === 'favoritos' ? 'ativo' : '' ?>">
                    <i class="fa-solid fa-star" style="color: var(--gold)"></i>Vídeos salvos
                </a>
            </li>
        </ul>
    </section>

    <section class="side-bloco">
        <h2 class="side-titulo">Contagem regressiva VESTIBULARES</h2>
        <ul class="contagem">
            <?php foreach ($PROVAS as $p): ?>
                <li class="prova prova-<?= e($p['cor']) ?>">
                    <span class="prova-nome"><?= e($p['rotulo']) ?></span>
                    <span class="prova-tempo" data-alvo="<?= e($p['data']) ?>">--</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</aside>