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

            <!-- HERO (a ilustração vem do CSS: assets/img/index/) -->
            <section class="hero">
                <div class="container">
                    <?php if ($logado): ?>
                        <!-- VISÃO: USUÁRIO LOGADO -->
                        <div class="hero-text is-logado">
                            <h1>Bem-vindo(a), <?= htmlspecialchars($_SESSION['nome']) ?>!</h1>
                            <p class="hero-desc">
                                <strong>Que bom ter você de volta!</strong> Aproveite nossos recursos para
                                organizar seu cronograma de estudos e revisar as leituras obrigatórias.
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- VISÃO: VISITANTE -->
                        <div class="hero-text">
                            <h1>Sua jornada estudantil começa <span class="destaque">aqui!</span></h1>
                            <p class="hero-sub">Melhore sua relação com os estudos e organize sua rotina com o Phronesis.</p>
                            <a href="cadastro.php" class="btn-hero">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                Criar conta
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- O QUE OFERECEMOS -->
            <main class="oferecemos-section">
                <div class="container">
                    <h1 class="titulo-principal">O que <span class="destaque">oferecemos</span>?</h1>
                    <h3 class="subtitulo-principal">Tudo o que você precisa para organizar seus estudos.</h3>

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
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
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
                            <h2>Acessibilidade</h2>
                            <p>Deixe o nosso site o mais confortavel para você!</p>
                            <a href="perfil/configperfil.php" class="servico-link">Ver acessibilidade →</a>
                        </article>

                    </div>
                </div>
            </main>

            <!-- RODAPÉ -->
            <footer class="site-footer">
                <span>© 2026 Phronesis_ • Todos os direitos reservados</span>
                <span class="footer-slogan">Disciplina hoje, liberdade amanhã.</span>
            </footer>

        </div>
    </div>

    <script src="assets/js/lib/index.js"></script>
    <?php include 'perfil/vlibras.php'; ?>

</body>
</html>