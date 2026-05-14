<?php
session_start();

// 1. Verifica se a variável de sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// --- CONEXÃO ---
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "phronesis");

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$id = $_SESSION['id'];
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

// Se a sessão existe mas o banco não retornou ninguém (ex: após um drop table)
if (!$dadosUser) {
    session_destroy(); // Destrói a sessão inválida
    header("Location: ../login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONEXÃO E BUSCA DO USUÁRIO ---
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "phronesis");
$dadosUser = null;

// Como a trava acima já garante que o ID existe, podemos buscar direto
$id = $_SESSION['id'];
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leituras obrigatórias</title>
    <link rel="stylesheet" href="../assets/css/livros.css">
    <link rel="stylesheet" href="../assets/css/header.css"> 

</head>
<body> 
    <?php include '../interface/header.php'; ?>

    <div class="wrapper">
        <div class="principalContent">
            <section class="banner">
                <img src="../assets/img/bannerLivros02.webp" class="bg-html" alt="Fundo">
            </section>

            <main class="areaContent">
                    <section class="listaLivros">
                        <article class="containerVest">
                            <div class="fuvestImg">
                                <img id="openModalF" src="../assets/img/fuvestLogo.webp" alt="FUVEST">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">FUVEST</h2>
                                <p>
                                    A Fuvest é o principal exame de ingresso para a USP, sendo considerada uma das provas mais tradicionais e exigentes do Brasil.
                                    Ela serve para selecionar alunos com alto nível de domínio acadêmico em diversas áreas do conhecimento.
                                </p>
                            </div>

                            <dialog id="modal">
                                <button type="button" id="closeModal" class="close-button">&times;</button>
                                <h2>FUVEST</h2>
                                <h3>Leituras Obrigatórias</h3>
                                <h3>Fuvest 2027</h3>
                                <p>
                                    <b>Opúsculo Humanitário (1853)</b> – Nísia Floresta
                                    <b>Nebulosas (1872)</b> – Narcisa Amália
                                    <b>Memórias de Martha (1899)</b>– Julia Lopes de Almeida
                                    <b>Caminho de Pedras (1937)</b>– Rachel de Queiroz
                                    <b>O Cristo Cigano (1961)</b>– Sophia de Mello Breyner Andresen
                                    <b>As Meninas (1973) </b>– Lygia Fagundes Telles
                                    <b>Balada de Amor ao Vento (1990)</b>– Paulina Chiziane
                                    <b>Canção para Ninar Menino Grande (2018)</b>– Conceição Evaristo
                                    <b>A Visão das Plantas (2019)</b>– Djaimilia Pereira de Almeida
                                </p>
                            </dialog>

                        </article>

                        <article class="containerVest">
                            <div class="vunestImg">
                                <img id="openModalV" src="../assets/img/vunespLogo.webp" alt="VUNESP">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">VUNESP</h2>
                                <p>
                                    A fundação Vunesp organiza o vestibular da Universidade Estadual Paulista.
                                    O exame busca avaliar a capacidade de análise crítica e a base de conhecimentos gerais do candidato,
                                    mantendo um equilíbrio entre o conteúdo teórico e a aplicação prática.
                                </p>
                            </div>

                            <dialog id="modal">
                                <button type="button" id="closeModal" class="close-button">&times;</button>
                                <h2>VUNESP</h2>
                                <h3>Leituras Obrigatórias</h3>
                                <h3>Fuvest 2027</h3>
                                <p>Conteúdo da janela modal.</p>
                            </dialog>
                        </article>

                        <article class="containerVest">
                            <div class="unicampImg">
                                <img id="openModalU" src="../assets/img/unicampLogo.webp" alt="UNICAMP">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">UNICAMP</h2>
                                <p>
                                    O vestibular da Unicamp é conhecido por sua abordagem interdisciplinar e humanizada.
                                    Ele serve para selecionar estudantes para uma das universidades de maior prestígio em pesquisa na América Latina,
                                    valorizando a capacidade de leitura e a interpretação de textos complexos.
                                </p>
                            </div>

                            <dialog id="modal">
                                <button type="button" id="closeModal" class="close-button">&times;</button>
                                <h2>UNICAMP</h2>
                                <h3>Leituras Obrigatórias</h3>
                                <h3>UNICAMP 2027</h3>
                                <p>
                                    <b>A vida não é util</b>- Ailton Krenak
                                    <b>Prosas seguidas de odes mínimas</b>- José Paulo Paes
                                    <b>Morangos mofados (contos escolhidos*)</b>- Caio Fernando Abreu
                                    <b>Vida e morde de M.J. Gonzaga de Sá</b>- Lima Barreto
                                    <b>No seu pescoço </b>- Chimamanda Ngozi Adichie
                                    <b>Olhos d’água </b>- Conceição Evaristo
                                    <b>Memórias Póstumas de Brás Cubas</b>- Machado de Assis
                                    <b>Canções escolhidas**</b>- Paulo César Pinheiro
                                    <b>Gabriel García Márquez</b>- Os funerais da Mamãe Grande
                                </p>
                            </dialog>
                        </article>

                        <article class="containerVest">
                            <div class="enemImg">
                                <img id="openModalE" src="../assets/img/enemLogo.webp" alt="ENEM">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">ENEM</h2>
                                <p>
                                    O ENEM é a maior porta de entrada para o ensino superior no Brasil.
                                    Ele serve para o ingresso em universidades federais e estaduais via Sisu,
                                    além de dar acesso a bolsas pelo ProUni e financiamentos pelo Fies.
                                </p>
                            </div>

                            <dialog id="modal">
                                <button type="button" id="closeModal" class="close-button">&times;</button>
                                <h2>ENEM</h2>
                                <p>Conteúdo da janela modal.</p>
                            </dialog>
                        </article>
                    </section>
            </main>
        </div>
    </div>

   <script>
document.addEventListener("DOMContentLoaded", function() {
    
    // === 1. LÓGICA DO DROPDOWN (HEADER) ===
    const perfilBtn = document.getElementById("perfilBtn");
    const dropdown = document.getElementById("dropdownPerfil");

    if (perfilBtn && dropdown) {
        perfilBtn.addEventListener("click", function(event) {
            event.stopPropagation();
            dropdown.classList.toggle("show");
        });

        window.addEventListener("click", function() {
            if (dropdown.classList.contains("show")) {
                dropdown.classList.remove("show");
            }
        });
    }

    // === 2. LÓGICA DAS JANELAS MODAIS (LIVROS) ===
    const containersVest = document.querySelectorAll('.containerVest');

    containersVest.forEach(container => {
        // Busca os elementos dentro de cada container específico
        const btnOpen = container.querySelector('img[id^="openModal"]');
        const modal = container.querySelector('dialog');
        const btnClose = container.querySelector('.close-button'); // Usando a classe que você definiu

        if (btnOpen && modal) {
            btnOpen.addEventListener("click", () => {
                modal.showModal();
            });
        }

        if (btnClose && modal) {
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
    });
});
</script>
</body>
</html>