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
                <a>CH</a>
                <a>CN</a>
                <a>LINGUAGENS</a>
                <a>MATEMÁTICA</a>
            </nav>
        </aside>
    
        <div class="principalContent">
            <header>
                <nav class="menuNav">
                    <ul class="menu">
                        <li class="materiais"><a href="material.php">Materiais</a></li>
                        <li class="home"><a href="../index.php">Início</a></li>
                        <li class="cronogramas"><a href="cronograma.php">Cronogramas</a></li>
                        <li class="entrar"><a href="../login.php">Entrar</a></li>
                    </ul>
                </nav>
            </header>

            <main class="areaContent">
                <section class="banner">
                    <div></div>
                </section>

                    <section class="listaLivros">
                        <article class="containerVest">
                            <div class="fuvestImg">
                                <img id="openModalF" src="../assets/img/fuvestLogo.webp" alt="FUVEST">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">FUVEST</h2>
                                <p>Description for FUVEST.</p>
                            </div>

                            <dialog id="modal">
                                <h2>FUVEST</h2>
                                <h3>Leituras Obrigatórias</h3>
                                <h3>Fuvest 2027</h3>
                                <p>
                                    Opúsculo Humanitário (1853) – Nísia Floresta
                                    Nebulosas (1872) – Narcisa Amália
                                    Memórias de Martha (1899) – Julia Lopes de Almeida
                                    Caminho de Pedras (1937) – Rachel de Queiroz
                                    O Cristo Cigano (1961) – Sophia de Mello Breyner Andresen
                                    As Meninas (1973) – Lygia Fagundes Telles
                                    Balada de Amor ao Vento (1990) – Paulina Chiziane
                                    Canção para Ninar Menino Grande (2018) – Conceição Evaristo
                                    A Visão das Plantas (2019) – Djaimilia Pereira de Almeida
                                </p>
                            </dialog>

                        </article>

                        <article class="containerVest">
                            <div class="vunestImg">
                                <img id="openModalV" src="../assets/img/vunespLogo.webp" alt="VUNESP">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">VUNESP</h2>
                                <p>Description for VUNESP.</p>
                            </div>

                            <dialog id="modal">
                            <h2>Título da Modal</h2>
                            <p>Conteúdo da janela modal.</p>
                            <button id="closeModal">Fechar</button>
                            </dialog>
                        </article>

                        <article class="containerVest">
                            <div class="unicampImg">
                                <img id="openModalU" src="../assets/img/unicampLogo.webp" alt="UNICAMP">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">UNICAMP</h2>
                                <p>Description for UNICAMP.</p>
                            </div>

                            <dialog id="modal">
                            <h2>Título da Modal</h2>
                            <p>Conteúdo da janela modal.</p>
                            <button id="closeModal">Fechar</button>
                            </dialog>
                        </article>

                        <article class="containerVest">
                            <div class="enemImg">
                                <img id="openModalE" src="../assets/img/enemLogo.webp" alt="ENEM">
                            </div>

                            <div class="descricaoVest">
                                <h2 class="nome">ENEM</h2>
                                <p>Description for ENEM.</p>
                            </div>

                            <dialog id="modal">
                            <h2>Título da Modal</h2>
                            <p>Conteúdo da janela modal.</p>
                            <button id="closeModal">Fechar</button>
                            </dialog>
                        </article>
                    </section>
            </main>
        </div>
    </div>

   <script>
        // Selecionamos todos os containers de vestibulares
        const containersVest = document.querySelectorAll('.containerVest');

        containersVest.forEach(container => {
            // Para cada container, buscamos o botão (imagem) e o modal específicos dele
            const btnOpen = container.querySelector('img[id^="openModal"]');
            const modal = container.querySelector('dialog');
            const btnClose = container.querySelector('#closeModal');

            // Abre o modal referente a este container
            if (btnOpen && modal) {
                btnOpen.addEventListener("click", () => {
                    modal.showModal();
                });
            }

            // Fecha o modal referente a este container
            if (btnClose && modal) {
                btnClose.addEventListener("click", () => {
                    modal.close();
                });
            }

            // FECHAR AO CLICAR FORA: Se clicar no fundo escuro (backdrop), o modal fecha
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