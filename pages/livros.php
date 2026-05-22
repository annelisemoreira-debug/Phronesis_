<?php
session_start();

// 1. Verifica se a variável de sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Se o usuário NÃO estiver logado, manda ele para a tela de login
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit(); 
}

// --- CONEXÃO ÚNICA ---
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "phronesis");

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$id = $_SESSION['id'];
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

// Se a sessão existe mas o banco não retornou ninguém (ex: após um drop table)
if (!$dadosUser) {
    session_destroy(); 
    header("Location: ../login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leituras obrigatórias</title>
    <link class="open-modal-trigger" rel="stylesheet" href="../assets/css/livros.css">
    <link rel="stylesheet" href="../assets/css/header.css"> 
</head>
<body> 
    <?php include '../interface/header.php'; ?>

    <div class="wrapper">
        <div class="principalContent">
            
            <section class="banner">
                <img id="bannerImg" src="../assets/img/bannerLivros02.webp" class="bg-html" alt="Fundo">
            </section>

            <main class="areaContent">
                <section class="listaLivros">
                    
                    <article class="containerVest">
                        <div class="fuvestImg">
                            <img id="logoFuvest" class="open-modal-trigger" src="../assets/img/fuvestLogo.webp" alt="FUVEST">
                        </div>
                        <div class="descricaoVest">
                            <h2 class="nome">FUVEST</h2>
                            <p>
                                A Fuvest (Fundação Universitária para o Vestibular) é o principal exame de ingresso para a Universidade de São Paulo (USP), uma das instituições de ensino superior mais prestigiadas da América Latina. Sendo considerada uma das provas mais tradicionais, concorridas e exigentes do Brasil, ela visa selecionar alunos com alto nível de domínio acadêmico e forte capacidade de raciocínio crítico. <span class="texto-ler-mais open-modal-trigger">Ver obras...</span>
                            </p>
                        </div>

                        <dialog id="modal-fuvest">
                            <button type="button" class="close-button">&times;</button>
                            <h2>FUVEST</h2>
                            <h3>Leituras Obrigatórias</h3>
                            <h3>Fuvest 2027</h3>
                            <p>
                                <b>Opúsculo Humanitário (1853)</b> – Nísia Floresta
                                <b>Nebulosas (1872)</b> – Narcisa Amália
                                <b>Memórias de Martha (1899)</b> – Julia Lopes de Almeida
                                <b>Caminho de Pedras (1937)</b> – Rachel de Queiroz
                                <b>O Cristo Cigano (1961)</b> – Sophia de Mello Breyner Andresen
                                <b>As Meninas (1973)</b> – Lygia Fagundes Telles
                                <b>Balada de Amor ao Vento (1990)</b> – Paulina Chiziane
                                <b>Canção para Ninar Menino Grande (2018)</b> – Conceição Evaristo
                                <b>A Visão das Plantas (2019)</b> – Djaimilia Pereira de Almeida
                            </p>
                        </dialog>
                    </article>

                    <article class="containerVest">
                        <div class="vunestImg">
                            <img id="logoVunesp" class="open-modal-trigger" src="../assets/img/vunespLogo.webp" alt="VUNESP">
                        </div>
                        <div class="descricaoVest">
                            <h2 class="nome">VUNESP</h2>
                            <p>
                                O vestibular da Universidade Estadual Paulista (Unesp), organizado e executado pela Fundação Vunesp, figura como um dos processos seletivos mais importantes, concorridos e respeitados do Brasil. Longe de ser apenas uma ferramenta de triagem mecânica, o exame é estruturado para avaliar a maturidade intelectual, a capacidade de análise crítica e a sólida base de conhecimentos gerais do candidato. <span class="texto-ler-mais open-modal-trigger">Ver obras...</span>
                            </p>
                        </div>

                        <dialog id="modal-vunesp">
                            <button type="button" class="close-button">&times;</button>
                            <h2>VUNESP</h2>
                            <h3>Leituras Obrigatórias</h3>
                            <h3>Vunesp 2027</h3>
                            <p>Conteúdo da janela modal.</p>
                        </dialog>
                    </article>

                    <article class="containerVest">
                        <div class="unicampImg">
                            <img id="logoUnicamp" class="open-modal-trigger" src="../assets/img/unicampLogo.webp" alt="UNICAMP">
                        </div>
                        <div class="descricaoVest">
                            <h2 class="nome">UNICAMP</h2>
                            <p>
                                O processo seletivo da Universidade Estadual de Campinas (Unicamp), coordenado pela Comvest (Comissão Permanente para os Vestibulares), ultrapassa os limites de um exame de admissão tradicional. Ele é projetado como um espelho da própria identidade da universidade: uma instituição de vanguarda, líder em patentes, inovação tecnológica e produção científica na América Latina. <span class="texto-ler-mais open-modal-trigger">Ver obras...</span>
                            </p>
                        </div>

                        <dialog id="modal-unicamp">
                            <button type="button" class="close-button">&times;</button>
                            <h2>UNICAMP</h2>
                            <h3>Leituras Obrigatórias</h3>
                            <h3>UNICAMP 2027</h3>
                            <p>
                                <b>A vida não é útil</b> - Ailton Krenak
                                <b>Prosas seguidas de odes mínimas</b> - José Paulo Paes
                                <b>Morangos mofados (contos escolheram*)</b> - Caio Fernando Abreu
                                <b>Vida e morte de M.J. Gonzaga de Sá</b> - Lima Barreto
                                <b>No seu pescoço</b> - Chimamanda Ngozi Adichie
                                <b>Olhos d’água</b> - Conceição Evaristo
                                <b>Memórias Póstumas de Brás Cubas</b> - Machado de Assis
                                <b>Canções escolhidas**</b> - Paulo César Pinheiro
                                <b>Gabriel García Márquez</b> - Os funerais da Mamãe Grande
                            </p>
                        </dialog>
                    </article>

                    <article class="containerVest">
                        <div class="enemImg">
                            <img id="logoEnem" class="open-modal-trigger" src="../assets/img/enemLogo.webp" alt="ENEM">
                        </div>
                        <div class="descricaoVest">
                            <h2 class="nome">ENEM</h2>
                            <p>
                               O Exame Nacional do Ensino Médio (ENEM), instituído em 1998 e profundamente reformulado em 2009, consolidou-se como a maior e mais importante porta de entrada para o ensino superior no Brasil, figurando também como um dos maiores exames unificados do mundo. <span class="texto-ler-mais open-modal-trigger">Ver obras...</span>
                            </p>
                        </div>

                        <dialog id="modal-enem">
                            <button type="button" class="close-button">&times;</button>
                            <h2>ENEM</h2>
                            <h3>Informações</h3>
                            <p>Conteúdo da janela modal.</p>
                        </dialog>
                    </article>

                </section>
            </main>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // === 1. LÓGICA DAS JANELAS MODAIS MÚLTIPLAS ===
    const containersVest = document.querySelectorAll('.containerVest');

    containersVest.forEach(container => {
        const triggers = container.querySelectorAll('.open-modal-trigger');
        const modal = container.querySelector('dialog');
        const btnClose = container.querySelector('.close-button');

        // Garante que o código só roda se o modal existir neste container específico
        if (modal) {
            if (triggers.length > 0) {
                triggers.forEach(trigger => {
                    trigger.addEventListener("click", () => {
                        modal.showModal();
                    });
                });
            }

            if (btnClose) {
                btnClose.addEventListener("click", () => {
                    modal.close();
                });
            }

            // Fechar ao clicar fora do modal (no backdrop)
            modal.addEventListener("click", (e) => {
                const dialogDimensions = modal.getBoundingClientRect();
                if (
                    e.clientX < dialogDimensions.left ||
                    e.clientX > dialogDimensions.right ||
                    e.clientY < dialogDimensions.top ||
                    e.clientY > dialogDimensions.bottom
                ) {
                    modal.close();
                }
            });
        }
    });

    // === 2. TROCA DE IMAGENS DO MODO ESCURO (APÓS CARREGAR O HTML) ===
    if (localStorage.getItem('site-theme') === 'dark') {
        
        // Substitui o banner clássico pelo escuro
        const banner = document.getElementById('bannerImg');
        if (banner) {
            banner.src = "../assets/img/bannerLivrosEscuro.webp";
        }

        // Substitui as logos dos vestibulares pelas versões escuras
        const fuv = document.getElementById('logoFuvest');
        const vun = document.getElementById('logoVunesp');
        const uni = document.getElementById('logoUnicamp');
        const ene = document.getElementById('logoEnem');

        if (fuv) fuv.src = "../assets/img/fuvestLogoEscuro.webp";
        if (vun) vun.src = "../assets/img/vunespLogoEscuro.webp";
        if (uni) uni.src = "../assets/img/unicampLogoEscuro.webp";
        if (ene) ene.src = "../assets/img/enemLogoEscuro.webp";
    }
});

// Mantém apenas a injeção da classe no topo para evitar "piscada" visual de fundo branco
if (localStorage.getItem('site-theme') === 'dark') {
    document.documentElement.classList.add('dark-theme');
}
</script>
</body>
</html>