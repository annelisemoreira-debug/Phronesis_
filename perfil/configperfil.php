<?php
session_start();

// 1. VERIFICAÇÃO DE ACESSO
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// 2. CONEXÃO COM O BANCO
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Erro: " . $conn->connect_error); }

$id_usuario = $_SESSION['id'];

// 3. LÓGICA DE UPLOAD (SEM ACUMULAR ARQUIVOS)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $diretorio = "assets/img/perfis/";
    if (!file_exists($diretorio)) { mkdir($diretorio, 0777, true); }

    // Busca foto antiga para deletar antes de subir a nova
    $busca = $conn->prepare("SELECT foto FROM usuario WHERE id_usuario = ?");
    $busca->bind_param("i", $id_usuario);
    $busca->execute();
    $fotoAntiga = $busca->get_result()->fetch_assoc()['foto'];

    if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp' && file_exists($fotoAntiga)) {
        unlink($fotoAntiga);
    }

    $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
    $novoNome = "user_" . $id_usuario . "_" . time() . "." . $extensao;
    $caminhoCompleto = $diretorio . $novoNome;

    if (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])) {
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminhoCompleto)) {
            $upd = $conn->prepare("UPDATE usuario SET foto = ? WHERE id_usuario = ?");
            $upd->bind_param("si", $caminhoCompleto, $id_usuario);
            $upd->execute();
            header("Location: configperfil.php");
            exit();
        }
    }
}

// 4. BUSCA DADOS PARA EXIBIÇÃO IMEDIATA
$stmt = $conn->prepare("SELECT nome, email, foto FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Esta linha garante que a imagem atual apareça ou a padrão caso não exista uma
$fotoExibicao = (!empty($user['foto'])) ? $user['foto'] : "assets/img/default-user.webp";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Phronesis</title>
    <link rel="stylesheet" href="assets/css/configperfil.css">
</head>
<body>
    <div class="main-wrapper">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/img/phronesisLogo.png" alt="Phronesis">
            </div>
            <nav>
                <p class="nav-title">Conta Pessoal</p>
                <ul>
                    <li><a href="configperfil.php" class="active">Meu perfil</a></li>
                    <li><a href="#">Acessibilidade</a></li>
                </ul>
                <p class="nav-title">Voltar</p>
                <ul>
                    <li><a href="../index.php">Início</a></li>
                    <li><a href="../pages/livros.php">Leituras Obrigatórias</a></li>
                    <li><a href="../pages/material.php">Materiais</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="profile-banner">
                <img src="assets/img/bannerMeuPerfil.png" alt="Banner Meu Perfil" class="banner-img" style="width: 100%;">
            </header>

            <section class="container-dados" style="padding: 20px;">
                <div class="alert-info">
                    <div class="text">
                        <strong>Sua conta é gerenciada pela organização da qual você faz parte.</strong>
                        <p>Seu nome e e-mail são controlados pelo provedor de identidade. Entre em contato com o administrador para alterações.</p>
                    </div>
                </div>
                
                <div class="profile-upload" style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                    <div class="avatar-wrapper">
                        <img src="<?php echo $fotoExibicao; ?>?t=<?php echo time(); ?>" 
                             alt="Foto de Perfil" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc; display: block;">
                    </div>
                    <div class="upload-info">
                        <h3>Faça upload da sua foto de perfil</h3>
                        <p>Ajuste sua imagem de exibição para facilitar a identificação.</p>
                        
                        <form action="configperfil.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="foto_perfil" id="foto_perfil" hidden onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('foto_perfil').click()" style="padding: 10px 20px; cursor: pointer; border-radius: 5px; border: 1px solid #ccc; background-color: #f9f9f9;">
                                Selecionar Nova Foto
                            </button>
                        </form>
                    </div>
                </div>

                <div class="user-info-fields">
                    <div class="field" style="margin-bottom: 15px;">
                        <label><strong>Nome:</strong></label>
                        <p><?php echo htmlspecialchars($user['nome']); ?></p>
                    </div>
                    <div class="field" style="margin-bottom: 15px;">
                        <label><strong>Endereço de e-mail:</strong></label>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <div class="footer-actions" style="margin-top: 40px;">
                    <a href="logout.php" style="color: red; text-decoration: none; font-weight: bold;">Desconectar</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>