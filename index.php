<?php
session_start();

// A página é pública: visitantes veem o banner com "Criar conta",
// usuários logados veem o painel de boas-vindas no lugar.
$logado = isset($_SESSION['id']) && isset($_SESSION['nome']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis</title>
    <link class="js-theme-style" rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="icon" type="image/png" href="assets/img/logos/favicon.png">

    <script>
        // Anti-flicker: aplica tema e tamanho de fonte antes de pintar a página
        (function () {
            var root = document.documentElement;
            if (localStorage.getItem('site-theme') === 'dark') root.classList.add('dark-theme');
            root.classList.add('font-' + (localStorage.getItem('site-font-size') || 'normal'));
        })();
    </script>
</head>

<body>
    <?php include 'interface/header.php'; ?>

    <div class="wrapper">
        <div class="principalContent">

            <!-- BANNER / HERO -->
            <section class="banner">
                <img src="assets/img/banners/bannerInicio.png" alt="" class="banner-img banner-light">
                <img src="assets/img/banners/escurophronesis.webp" alt="" class="banner-img banner-dark">

                <div class="banner-content">
                    <div class="banner-text">
                        <?php if ($logado): ?>
                            <!-- VISÃO: USUÁRIO LOGADO -->
                            <span class="banner-tag">PAINEL DO ESTUDANTE</span>
                            <h1>Bem-vindo(a), <?= htmlspecialchars($_SESSION['nome']) ?>!</h1>
                            <p class="banner-desc">
                                <strong>Que bom ter você de volta!</strong> Aproveite nossos recursos para
                                organizar seu cronograma de estudos e revisar as leituras obrigatórias.
                            </p>
                        <?php else: ?>
                            <!-- VISÃO: VISITANTE -->
                            <h1>Sua jornada estudantil começa aqui!</h1>
                            <p>Melhore sua relação com os estudos</p>
                            <a href="cadastro.php" class="btn-hero">Criar conta</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- O QUE OFERECEMOS -->
            <main class="oferecemos-section">
                <h1 class="titulo-principal">O que oferecemos?</h1>
                <h3 class="subtitulo-principal">Conheça nossos serviços e melhore nos estudos!</h3>

                <div class="cards-grid">

                    <article class="servico-card">
                        <div class="servico-icone icone-roxo">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 4h7a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H2z"/><path d="M22 4h-7a3 3 0 0 0-3 3v13a2 2 0 0 1 2-2h8z"/></svg>
                        </div>
                        <h2>Materiais de estudo</h2>
                        <p>Videoaulas organizadas por disciplina, que permitem estudar no seu próprio ritmo.</p>
                        <a href="pages/material.php" class="servico-link">Explorar materiais →</a>
                    </article>

                    <article class="servico-card">
                        <div class="servico-icone icone-amarelo">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </div>
                        <h2>Obras obrigatórias</h2>
                        <p>Materiais completos sobre as leituras obrigatórias dos principais vestibulares.</p>
                        <a href="pages/livros.php" class="servico-link">Ver obras →</a>
                    </article>

                    <article class="servico-card">
                        <div class="servico-icone icone-roxo">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        </div>
                        <h2>Cronogramas</h2>
                        <p>Você informa seus objetivos e a plataforma organiza a sua rotina de estudos.</p>
                        <a href="pages/cronograma.php" class="servico-link">Criar cronograma →</a>
                    </article>

                    <article class="servico-card">
                        <div class="servico-icone icone-azul">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="15 7 21 7 21 13"/></svg>
                        </div>
                        <h2>Meu desempenho</h2>
                        <p>Acompanhe sua evolução e descubra o que precisa ser revisado.</p>
                        <a href="pages/desempenho.php" class="servico-link">Ver desempenho →</a>
                    </article>

                </div>
            </main>

            <!-- SOBRE NÓS (faixa clara com cantos arredondados que aparece no rodapé da imagem) -->
            <footer class="sobre-nos-section">
                <div class="sobre-nos-content">
                    <h2>@2026 Phronesis - Todos os direitos reservados</h2>
                    <p class="description">
                        Annelise Ramos - João Gustavo - Manuella Vitoria - Pinesso
                    </p>
                </div>
            </footer>

        </div>
    </div>

    <script src="assets/js/lib/index.js"></script>
    <?php include 'perfil/vlibras.php'; ?>

</body>
</html>