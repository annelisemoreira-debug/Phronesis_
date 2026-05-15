<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/header.css"> 
</head>
<body>
    <?php include 'interface/header.php'; ?>

    <main>
        <section class="banner">
            <img src="assets/img/bannerInicio.webp" alt="Fundo">
        </section>
        <h1> O que oferecemos? </h1>
        <h3>Conheça nosso serviços e melhore nos estudos!</h3>
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

    <script>
document.addEventListener("DOMContentLoaded", function() {
    const perfilBtn = document.getElementById("perfilBtn");
    const dropdown = document.getElementById("dropdownPerfil");

    if (perfilBtn && dropdown) {
        // 1. Clique para Abrir/Fechar
        perfilBtn.addEventListener("click", function(event) {
            event.stopPropagation();
            dropdown.classList.toggle("show");
            console.log("Menu clicado!"); // Debug: Verifique no F12 se isso aparece
        });

        // 2. Clique fora para fechar
        window.addEventListener("click", function() {
            if (dropdown.classList.contains("show")) {
                dropdown.classList.remove("show");
            }
        });
    } else {
        console.error("Elementos do menu não encontrados!");
    }

    // --- Lógica do Carrossel (Mantida) ---
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
</body>
</html>