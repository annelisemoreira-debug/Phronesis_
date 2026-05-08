<?php
session_start();

// 1. CONEXÃO COM O BANCO
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

// 2. LÓGICA DA FOTO E TIPO
$fotoPerfil = "assets/img/default-user.webp"; 
$usuarioTipo = "comum"; // Valor padrão caso não encontre

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];
    
    // ADICIONADO 'tipo' na consulta abaixo
    $stmt = $conn->prepare("SELECT foto, tipo FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $usuarioTipo = $res['tipo']; // Armazena se é 'admin' ou 'comum'
        
        if (!empty($res['foto'])) {
            $caminhoFoto = $res['foto'];
            if (file_exists($caminhoFoto)) {
                $fotoPerfil = $caminhoFoto;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        /* Seus estilos originais permanecem aqui */
        .perfil-container { position: flex; cursor: pointer; }
        .foto-perfil { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ccc; object-fit: cover; }
        .menu-dropdown { display: none; position: absolute; right: 0; background: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 5px; min-width: 150px; z-index: 100; }
        .menu-dropdown a { display: block; padding: 10px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; }
        .menu-dropdown a:hover { background: #f4f4f4; }
        .show { display: block; }
        
        /* Estilo para o item ADM não quebrar o layout */
        .menu .admin a { color: #ff4d4d; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <div class="logoMarca"><img src="assets/img/phronesisIndex.png" alt="Logo oficial da Phronesis"></div>
        <nav class="menuNav">
            <ul class="menu">
                <?php if (isset($_SESSION['id'])): ?>
                    <li class="materiais"><a href="pages/material.php">Materiais</a></li>
                    <li class="livros"><a href="pages/livros.php">Obras Obrigatórias</a></li>
                    <li class="cronogramas"><a href="pages/meu_calendario.php">Cronogramas</a></li>

                    <?php if ($usuarioTipo === 'admin'): ?>
                        <li class="admin"><a href="pages/admin_materias.php">ADM</a></li>
                    <?php endif; ?>

                    <li class="perfil-container" onclick="toggleMenu()">
                        <img src="<?php echo $fotoPerfil; ?>?t=<?php echo time(); ?>" alt="Perfil" class="foto-perfil">
                        <div id="dropdownPerfil" class="menu-dropdown">
                            <a href="perfil/configperfil.php">Meu Perfil</a>
                            <a href="logout.php" style="color: red;">Sair</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="entrar"><a href="login.php">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="banner">
            <img src="./assets/img/bannerInicio.png" alt="Fundo">
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
        function toggleMenu() {
            document.getElementById("dropdownPerfil").classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.foto-perfil')) {
                var dropdowns = document.getElementsByClassName("menu-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        const track = document.querySelector('.carousel-track');
        const nextBtn = document.querySelector('.next');
        const prevBtn = document.querySelector('.prev');

        function moveNext() {
            const cards = document.querySelectorAll('.card');
            track.appendChild(cards[0]);
        }

        function movePrev() {
            const cards = document.querySelectorAll('.card');
            track.insertBefore(cards[cards.length - 1], cards[0]);
        }

        nextBtn.addEventListener('click', moveNext);
        prevBtn.addEventListener('click', movePrev);
    </script>
</body>
</html>