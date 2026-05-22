<?php
// CONEXÃO
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";
$conn = new mysqli($servername, $username, $password, $dbname);

$script_name = $_SERVER['SCRIPT_NAME'];

// 2. Se NÃO estivermos no index.php principal, precisamos subir um nível
if (strpos($script_name, '/pages/') !== false || strpos($script_name, '/perfil/') !== false) {
    $path = "../";
} else {
    $path = ""; // Se estiver no index.php ou arquivos da raiz
}

// 3. Define a foto padrão usando o $path dinâmico de forma correta (apenas $path + assets)
$fotoPerfil = $path . "assets/img/default-user.webp"; 
$usuarioTipo = 'user'; // Valor padrão para evitar erros de Notice do PHP

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT foto, tipo FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    if ($res) {
        $usuarioTipo = $res['tipo']; 
        if (!empty($res['foto'])) {
            // Remove barras iniciais da foto do banco para não quebrar com o $path
            $fotoLimpa = ltrim($res['foto'], '/');
            $fotoPerfil = $path . $fotoLimpa;
        }
    }
}
?>

<header>
    <div class="logoMarca">
        <a href="<?php echo $path; ?>index.php">
            <img src="<?php echo $path; ?>assets/img/logoSite.webp" alt="logoPhronesis">
        </a>
    </div>
    <nav class="menuNav">
        <ul class="menu">
            <?php if (isset($_SESSION['id'])): ?>
                <li class="materiais"><a href="<?php echo $path; ?>pages/material.php">Materiais</a></li>
                <li class="livros"><a href="<?php echo $path; ?>pages/livros.php">Obras Obrigatórias</a></li>
                <li class="cronogramas"><a href="<?php echo $path; ?>pages/meu_calendario.php">Cronogramas</a></li>
                
                <?php if ($usuarioTipo === 'admin'): ?>
                    <li class="admin"><a href="<?php echo $path; ?>pages/admin_materias.php">ADM</a></li>
                <?php endif; ?>

                <li class="perfil-container" id="perfilBtn">
                    <img src="<?php echo $fotoPerfil; ?>" alt="Perfil" class="foto-perfil">
                    <div id="dropdownPerfil" class="menu-dropdown">
                        <a href="<?php echo $path; ?>perfil/configperfil.php">Meu Perfil</a>
                        <a href="<?php echo $path; ?>logout.php" style="color: red;">Sair</a>
                    </div>
                </li>
            <?php else: ?>
                <li class="entrar"><a href="<?php echo $path; ?>login.php">Entrar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const perfilBtn = document.getElementById("perfilBtn");
    const dropdownPerfil = document.getElementById("dropdownPerfil");

    if (perfilBtn && dropdownPerfil) {
        // Abre e fecha ao clicar no container do perfil
        perfilBtn.addEventListener("click", function(e) {
            e.stopPropagation(); // Evita fechar imediatamente pelo clique global
            dropdownPerfil.classList.toggle("show");
        });

        // Fecha o menu caso o usuário clique em qualquer outro lugar da tela
        document.addEventListener("click", function(e) {
            if (!perfilBtn.contains(e.target)) {
                dropdownPerfil.classList.remove("show");
            }
        });
    }
});
</script>