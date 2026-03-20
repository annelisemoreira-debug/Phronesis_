<?php 
session_start(); 
// Aqui você poderia buscar o caminho da foto no banco de dados se tiver uma coluna para isso.
// Por enquanto, usaremos uma imagem padrão.
$fotoPerfil = "assets/img/default-user.webp"; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        /* Estilos rápidos para o menu de perfil */
        .perfil-container { position: flex; cursor: pointer; }
        .foto-perfil { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ccc; object-fit: cover; }
        .menu-dropdown { 
            display: none; position: absolute; right: 0; background: white; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 5px; min-width: 150px; z-index: 100;
        }
        .menu-dropdown a { display: block; padding: 10px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; }
        .menu-dropdown a:hover { background: #f4f4f4; }
        .show { display: block; }
    </style>
</head>
<body>
    <header>
        <div class="logoMarca"><img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis"></div>
        <nav class="menuNav">
            <ul class="menu">
                <li class="materiais"><a href="pages/material.php">Materiais</a></li>
                <li class="livros"><a href="pages/livros.php">Livros</a></li>
                <li class="cronogramas"><a href="pages/cronograma.php">Cronogramas</a></li>
    
                
                <?php if (isset($_SESSION['id'])): ?>
                    <li class="perfil-container" onclick="toggleMenu()">
                        
                        <img src="<?php echo $fotoPerfil; ?>" alt="Perfil" class="foto-perfil">
                        <div id="dropdownPerfil" class="menu-dropdown">
                            <a href="perfil.php">Meu Perfil</a>
                            <a href="configuracoes.php">Configurações</a>
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
        
        <section class="introducao">
            <div class="introducao-texto">
                <h1>Bem-vindo ao Phronesis!</h1>
                <p>Seu portal de estudos para concursos públicos. Encontre materiais, livros e cronogramas personalizados para alcançar seus objetivos.</p>
                <a href="cadastro.php" class="botaoCadastro">Cadastre-se Agora</a>
            </div>
            <div class="introducao-imagem">
                <img src="assets/img/estudo.webp" alt="Imagem de estudo">
            </div>
        </section>
    </main>
    
    <script>
        // Conteúdo principal da página
        // Função para abrir/fechar o menu ao clicar na foto
        function toggleMenu() {
            document.getElementById("dropdownPerfil").classList.toggle("show");
        }

        // Fecha o menu se o usuário clicar fora dele
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
    </script>
    </body>
</html>