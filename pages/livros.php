<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leituras obrigatórias</title>
    <link rel="stylesheet" href="../assets/css/livros.css">
</head>
<body> 
    <div class="wrapper">

        <aside class="menuSidebar">
            <div class="logoMarca">
                <a href="../index.php">
                    <img src="../assets/img/logoSidebar.webp" alt="Logo oficial da Phronesis">
                </a>
            </div>

            <nav>
                <a>Linguagens</a>
                <a>Matemática</a>
                <a>Física</a>
                <a>Biologia</a>
                <a>Química</a>
                <a>História</a>
                <a>Geografia</a>
            </nav>
        </aside>
    
        <div class="principalContent">
            <header>
                <nav class="menuNav">
                    <ul class="menu">
                        <li class="materiais"><a href="material.php">Materiais</a></li>
                        <li class="home"><a href="../index.php">Início</a></li>
                        <li class="cronogramas"><a href="meu_calendario.php">Cronogramas</a></li>
                        <li class="entrar"><a href="../login.php">Entrar</a></li>
                    </ul>
                </nav>
            </header>

            <main class="areaContent">
                <section class="banner">
                    <img src="../assets/img/bannerLivros02.png" class="bg-html" alt="Fundo">
                </section>

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
        const containersVest = document.querySelectorAll('.containerVest');

        containersVest.forEach(container => {
            const btnOpen = container.querySelector('img[id^="openModal"]');
            const modal = container.querySelector('dialog');
            const btnClose = container.querySelector('#closeModal');

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
    </script>
</body>
</html>