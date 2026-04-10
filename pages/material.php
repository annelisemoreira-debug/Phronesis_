<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/material.css">
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
                        <li class="livros"><a href="livros.php">Obras Obrigatórias</a></li>
                        <li class="home"><a href="../index.php">Início</a></li>
                        <li class="cronogramas"><a href="planejar.php">Cronogramas</a></li>
                        <li class="entrar"><a href="../login.php">Entrar</a></li>
                    </ul>
                </nav>
            </header>

            <main class="areaContent">
                <section class="banner">
                    <div></div>
                </section>

                <section class="container-materiais">
    
                    <div class="video-container-white">
                        <div class="video-header">
                            <h2 class="video-title">Materiais Complementares</h2>
                            <div class="search-box">
                                <input type="text" placeholder="Pesquisar vídeo..." id="searchInput">
                                <button type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="video-grid" id="videoGrid">
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                            <div class="video-card"></div>
                        </div>
                    </div>

                    <aside class="sidebar-complementar">
                        </aside>

                </section>
            </main>
        </div>
    </div>
</body>
</html>