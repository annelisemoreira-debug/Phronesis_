<?php
session_start();

// 1. Verifica se a variável de sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// --- CONEXÃO COM O BANCO ---
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Erro: " . $conn->connect_error); }

$id_usuario = $_SESSION['id'];

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id_usuario");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// 3. LÓGICA DE PROCESSAMENTO DO CORTE DA IMAGEM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'crop_image') {
    ob_clean(); 
    header('Content-Type: application/json');

    try {
        $diretorioFisico = "../assets/img/perfis/";
        $diretorioBanco = "assets/img/perfis/";
        
        if (!file_exists($diretorioFisico)) { 
            mkdir($diretorioFisico, 0777, true); 
        }

        $base64_image = $_POST['image_data']; 
        $data = explode(',', $base64_image);
        $image_content = base64_decode($data[1]);

        $nomeArquivo = "user_" . $id_usuario . "_" . time() . ".webp";
        $caminhoCompletoFisico = $diretorioFisico . $nomeArquivo;
        $caminhoBanco = $diretorioBanco . $nomeArquivo;

        if (file_put_contents($caminhoCompletoFisico, $image_content)) {
            $busca = $conn->prepare("SELECT foto FROM usuario WHERE id_usuario = ?");
            $busca->bind_param("i", $id_usuario);
            $busca->execute();
            $fotoAntiga = $busca->get_result()->fetch_assoc()['foto'];

            if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp') {
                $caminhoArquivoAntigo = "../" . $fotoAntiga;
                if (file_exists($caminhoArquivoAntigo)) { @unlink($caminhoArquivoAntigo); }
            }

            $upd = $conn->prepare("UPDATE usuario SET foto = ? WHERE id_usuario = ?");
            $upd->bind_param("si", $caminhoBanco, $id_usuario);
            $upd->execute();

            echo json_encode(['status' => 'success', 'redirect' => 'configperfil.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Não foi possível escrever o arquivo na pasta perfis.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 4. BUSCA DADOS PARA EXIBIÇÃO
$stmt = $conn->prepare("SELECT nome, email, foto FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$caminhoPadrao = "../assets/img/default-user.webp"; 
$fotoExibicao = (!empty($user['foto']) && file_exists("../" . $user['foto'])) ? "../" . $user['foto'] : $caminhoPadrao;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Phronesis</title>
    <link rel="stylesheet" href="../assets/css/configperfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    
    <script>
        (function () {
            const savedTheme = localStorage.getItem('site-theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>

    <style>
        .modal-crop {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.8); align-items: center; justify-content: center;
        }
        .modal-crop-content {
            background-color: #fff; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; text-align: center;
        }
        .img-container { max-height: 400px; margin-bottom: 20px; }
        .img-container img { max-width: 100%; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-crop-confirm { background-color: #7B61FF; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn-crop-cancel { background-color: #e0e0e0; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .cropper-view-box, .cropper-face { border-radius: 50%; outline: none !important; }
    </style>
</head>
<body>
   <div class="main-wrapper">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/img/logoSite.webp" alt="Phronesis" class="logo-light">
                <img src="../assets/img/logoescuro.webp" alt="Phronesis" class="logo-dark">
            </div>
            <nav>
                <p class="nav-title">Conta Pessoal</p>
                <ul>
                    <li><a href="configperfil.php" class="active"><span>👤</span> Meu perfil</a></li>
                    <li>
                        <a href="../perfil/acessibilidade.php" class="nav-link-acessibilidade">
                            <img src="../assets/img/acessibilidade.webp" alt="Acessibilidade" class="icon-acessibilidade">
                            <span>Acessibilidade</span>
                        </a>
                    </li>
                </ul>
                <p class="nav-title">Voltar</p>
                <ul>
                    <li><a href="../index.php"><span>🏠</span> Início</a></li>
                    <li><a href="../pages/livros.php"><span>📚</span> Livros</a></li>
                    <li><a href="../pages/material.php"><span>📝</span> Matérias</a></li>
                    <li><a href="../pages/cronograma.php"><span>📅</span> Cronograma</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="profile-banner">
                <img src="../assets/img/meuperfilclaro.webp" alt="Banner" class="banner-img banner-light">
                <img src="../assets/img/escuroperfil.webp" alt="Banner" class="banner-img banner-dark">
            </header>

            <section class="container-dados">
                <div class="alert-info">
                    <div class="text">
                        <strong>Sua conta é gerenciada pela organização da qual você faz parte.</strong>
                        <p>Seu nome e e-mail são controlados pelo provedor de identidade. Entre em contato com o administrador para alterações.</p>
                    </div>
                </div>

                <div class="profile-upload">
                    <div class="avatar-wrapper">
                        <img src="<?php echo $fotoExibicao; ?>?t=<?php echo time(); ?>" id="currentAvatar">
                    </div>
                    <div class="upload-info">
                        <h3>Faça upload da sua foto de perfil</h3>
                        <p>Ajuste sua imagem de exibição para facilitar a identificação.</p>
                        <form id="formFotoPerfil">
                            <input type="file" id="foto_perfil" hidden accept="image/*">
                            <button type="button" id="btnSelectFoto">Selecionar Nova Foto</button>
                        </form>
                    </div>
                </div>

                <div class="user-info-fields">
                    <div class="field">
                        <label><strong>Nome:</strong></label>
                        <p class="user-value"><?php echo htmlspecialchars($user['nome']); ?></p>
                    </div>
                    <div class="field">
                        <label><strong>Endereço de e-mail:</strong></label>
                        <p class="user-value"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <div class="footer-actions">
                    <a href="../logout.php">Desconectar</a>
                </div>
            </section>
        </main>
    </div>

    <div id="cropModal" class="modal-crop">
        <div class="modal-crop-content">
            <h3>Ajuste sua foto</h3>
            <div class="img-container"><img id="imageToCrop"></div>
            <div class="modal-footer">
                <button type="button" class="btn-crop-cancel" id="btnCancelCrop">Cancelar</button>
                <button type="button" class="btn-crop-confirm" id="btnConfirmCrop">Salvar</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
    <script>
        $(document).ready(function() {
            var $modal = $('#cropModal'), $image = $('#imageToCrop'), cropper;

            $('#btnSelectFoto').on('click', function() { $('#foto_perfil').click(); });

            $('#foto_perfil').on('change', function(e) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    $image.attr('src', event.target.result);
                    $modal.css('display', 'flex');
                    if (cropper) cropper.destroy();
                    cropper = new Cropper($image[0], { aspectRatio: 1, viewMode: 1, dragMode: 'move' });
                };
                reader.readAsDataURL(e.target.files[0]);
            });

            $('#btnCancelCrop').on('click', function() { $modal.hide(); cropper.destroy(); });

            $('#btnConfirmCrop').on('click', function() {
                var canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
                var base64data = canvas.toDataURL('image/webp');

                $.ajax({
                    type: 'POST',
                    url: 'configperfil.php',
                    data: { action: 'crop_image', image_data: base64data },
                    success: function(response) { window.location.href = response.redirect; }
                });
            });
        });
    </script>
</body>
</html>