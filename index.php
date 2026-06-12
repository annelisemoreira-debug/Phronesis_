<?php
session_start();

// Se o usuário NÃO estiver logado, manda ele para a tela de login
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit(); // Interrompe a execução do script
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis</title>
    <link class="js-theme-style" rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <script>
        // Anti-Flicker nativo executado no Head (Evita tela piscar em branco)
        if (localStorage.getItem('site-theme') === 'dark') {
            document.documentElement.classList.add('dark-theme');
        }
    </script>
</head>

<body>
    <?php include 'interface/header.php'; ?>

    <div class="wrapper">
        <div class="principalContent">
            
            <section class="banner">
                <img src="assets/img/bannerInicio.webp" alt="Fundo" class="banner-img banner-light">
                <img src="assets/img/escurophronesis.webp" alt="Fundo Escuro" class="banner-img banner-dark">
            </section>

            <section class="sobre-nos-container">
                <div class="page-wrapper">
                    <div class="bento-grid">
                        <div class="grid-col-left">
                            <div class="box item-1">
                                <img src="assets/img/vini.webp" alt="Pessoas">
                            </div>
                        </div>
                        <div class="grid-col-right">
                            <div class="box item-2">
                                <img src="assets/img/qualquer.webp" alt="Pessoas">
                            </div>
                            <div class="grid-row-bottom">
                                <div class="box item-3">
                                    <img src="assets/img/joao.webp" alt="Pessoas">
                                </div>
                                <div class="box item-4">
                                    <img src="assets/img/nos.webp" alt="Pessoas">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar">
                        <?php if (isset($_SESSION['id']) && isset($_SESSION['nome'])): ?>
                            <span class="tag">PAINEL DO ESTUDANTE</span>
                            <h1>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h1>
                            <p class="description">
                                Que bom ter você de volta! Aproveite nossos recursos para organizar seu cronograma de estudos e revisar as leituras obrigatórias.
                            </p>
                            <div class="mission-box">
                                <p>Sua evolução acadêmica começa aqui. Selecione uma das ferramentas abaixo para iniciar seus estudos de hoje.</p>
                            </div>
                        <?php else: ?>
                            <span class="tag">PROJETO FINAL • SENAI</span>
                            <h1>Sobre nós</h1>
                            <p class="description">
                                Somos desenvolvedores em formação, cursando o final do Ensino Médio e o 
                                <strong>Técnico em Desenvolvimento de Sistemas</strong> no SENAI.
                            </p>
                            <div class="mission-box">
                                <p>A Phronesis é o resultado do nosso TCC: um sistema pensado para aplicar todo o conhecimento técnico que adquirimos ao longo do curso.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <main class="carrossel-section">
                <h1 class="titulo-principal">O que oferecemos?</h1>
                <h3 class="subtitulo-principal">Conheça nossos serviços e melhore nos estudos!</h3>
                
                <div class="carousel-container">
                    <button class="btn prev">❮</button>
                    <div class="carousel-track">
                        
                        <div class="card blue-theme">
                            <div class="top-tabs-container">
                                <div class="tab blue"></div>
                                <div class="tab yellow"></div>
                                <div class="tab purple"></div>
                            </div>
                            <h2>Materiais</h2>
                            <p><b>• Videoaulas:</b><br> Aulas em vídeo organizadas por disciplina, que permitem estudar no próprio ritmo...</p>
                            <div class="bottom-tab"></div>
                        </div>

                        <div class="card purple-theme">
                            <div class="top-tabs-container">
                                <div class="tab purple"></div>
                                <div class="tab blue"></div>
                                <div class="tab yellow"></div>
                            </div>
                            <h2>Cronograma</h2>
                            <p><b>• Cronograma personalizado:</b><br> O aluno informa seus objetivos e a plataforma organiza...</p>
                            <div class="bottom-tab"></div>
                        </div>

                        <div class="card yellow-theme">
                            <div class="top-tabs-container">
                                <div class="tab yellow"></div>
                                <div class="tab purple"></div>
                                <div class="tab blue"></div>
                            </div>
                            <h2>Livros</h2>
                            <p><b>• Leituras obrigatórias:</b><br> Materiais completos sobre as leituras obrigatórias...</p>
                            <div class="bottom-tab"></div>
                        </div>
                    </div>
                    <button class="btn next">❯</button>
                </div>
            </main>
            
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- Lógica do Carrossel ---
        const track = document.querySelector('.carousel-track');
        const nextBtn = document.querySelector('.next');
        const prevBtn = document.querySelector('.prev');

        if (nextBtn && prevBtn) {
            nextBtn.addEventListener('click', () => {
                const cards = document.querySelectorAll('.card');
                track.appendChild(cards[0]);
            });
            prevBtn.addEventListener('click', () => {
                const cards = document.querySelectorAll('.card');
                track.insertBefore(cards[cards.length - 1], cards[0]);
            });
        }
    });
    </script>

    <?php include 'perfil/vlibras.php'; ?>

</body>
</html>