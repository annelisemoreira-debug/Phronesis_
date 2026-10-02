<?php
session_start();

// 1. Verifica se a variável de sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

$id_usuario = $_SESSION['id'];

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$res = $conn->query("SELECT foto, email, senha, tipo FROM usuario WHERE id_usuario = $id_usuario");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// =========================================================================
// 3. LOGICA DE PROCESSAMENTO DO BACK-END (APENAS PARA REQUISIÇÕES POST / AJAX)
// =========================================================================

// Identificação robusta: conta como Google se veio via sessão OU se a foto gravada for uma URL externa
$isGoogleUser = (isset($_SESSION['login_google']) && $_SESSION['login_google'] === true) || filter_var($dadosUser['foto'], FILTER_VALIDATE_URL);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    ob_clean(); 
    header('Content-Type: application/json');

    // --- SEGURANÇA: BLOQUEIO PARA USUÁRIOS GOOGLE ---
    if (($_POST['action'] == 'delete_image' || $_POST['action'] == 'crop_image') && $isGoogleUser) {
        echo json_encode(['status' => 'error', 'message' => 'Usuários do Google não podem alterar a foto de perfil.']);
        exit();
    }

    // --- AÇÃO: REMOVER FOTO (VOLTAR PADRÃO) ---
    if ($_POST['action'] == 'delete_image') {
        try {
            $busca = $conn->prepare("SELECT foto FROM usuario WHERE id_usuario = ?");
            $busca->bind_param("i", $id_usuario);
            $busca->execute();
            $fotoAntiga = $busca->get_result()->fetch_assoc()['foto'];

            // Só tenta deletar fisicamente se NÃO for uma URL externa do Google
            if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp' && !filter_var($fotoAntiga, FILTER_VALIDATE_URL)) {
                $caminhoArquivoAntigo = "../" . $fotoAntiga;
                if (file_exists($caminhoArquivoAntigo)) { @unlink($caminhoArquivoAntigo); }
            }

            $upd = $conn->prepare("UPDATE usuario SET foto = NULL WHERE id_usuario = ?");
            $upd->bind_param("i", $id_usuario);
            $upd->execute();

            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    // --- AÇÃO: CORTAR E SALVAR IMAGEM ---
    if ($_POST['action'] == 'crop_image') {
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

                // Só tenta deletar fisicamente se NÃO for uma URL externa do Google
                if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp' && !filter_var($fotoAntiga, FILTER_VALIDATE_URL)) {
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

    // --- AÇÃO: EXCLUIR CONTA TRADICIONAL ---
    if ($_POST['action'] == 'delete_account') {
        try {
            $password = $_POST['password'] ?? '';

            if (password_verify($password, $dadosUser['senha'])) {
                $fotoAntiga = $dadosUser['foto'];
                if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp' && !filter_var($fotoAntiga, FILTER_VALIDATE_URL)) {
                    $caminhoArquivoAntigo = "../" . $fotoAntiga;
                    if (file_exists($caminhoArquivoAntigo)) { @unlink($caminhoArquivoAntigo); }
                }

                $del = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
                $del->bind_param("i", $id_usuario);
                
                if ($del->execute()) {
                    session_destroy();
                    echo json_encode(['status' => 'success', 'redirect' => '../index.php']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar dados do banco.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Senha incorreta!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    // --- AÇÃO: EXCLUIR CONTA DIRETO (GOOGLE) ---
    if ($_POST['action'] == 'delete_account_google') {
        try {
            $fotoAntiga = $dadosUser['foto'];
            if ($fotoAntiga && $fotoAntiga != 'assets/img/default-user.webp' && !filter_var($fotoAntiga, FILTER_VALIDATE_URL)) {
                $caminhoArquivoAntigo = "../" . $fotoAntiga;
                if (file_exists($caminhoArquivoAntigo)) { @unlink($caminhoArquivoAntigo); }
            }

            $del = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
            $del->bind_param("i", $id_usuario);
            
            if ($del->execute()) {
                session_destroy();
                echo json_encode(['status' => 'success', 'redirect' => '../index.php']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar dados do banco.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }
}

// =========================================================================
// 4. BUSCA DADOS PARA EXIBIÇÃO ATUALIZADA
// =========================================================================
$stmt = $conn->prepare("SELECT nome, email, foto, senha, tipo FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$caminhoPadrao = "../assets/img/default-user.webp";

// Redimensiona a foto do Google para um tamanho adequado ao avatar
function otimizarFotoGoogle($url, $tamanho = 192) {
    if (strpos($url, 'googleusercontent.com') === false) {
        return $url;
    }
    // remove qualquer parâmetro de tamanho existente (=s96-c, =w100-h100, etc.)
    $url = preg_replace('/=[a-z0-9\-]+$/i', '', $url);
    return $url . '=s' . $tamanho . '-c';
}

// Tratamento dinâmico do caminho da foto
if (filter_var($user['foto'], FILTER_VALIDATE_URL)) {
    $fotoExibicao = otimizarFotoGoogle($user['foto']);
    $temFotoPersonalizada = false;
} else {
    $temFotoPersonalizada = (!empty($user['foto']) && $user['foto'] != 'assets/img/default-user.webp' && file_exists("../" . $user['foto']));
    $fotoExibicao = $temFotoPersonalizada ? "../" . $user['foto'] : $caminhoPadrao;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Phronesis</title>
    <link rel="stylesheet" href="../assets/css/configperfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.png">
    <link rel="preconnect" href="https://lh3.googleusercontent.com">
    <link rel="dns-prefetch" href="https://lh3.googleusercontent.com">
    
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
        .img-container img { max-width: 100%; display: block; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; }
        .btn-crop-confirm { background-color: #7B61FF; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-crop-cancel { background-color: #e0e0e0; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .cropper-view-box, .cropper-face { border-radius: 50%; outline: none !important; }
        .btn-remove-foto { color: #FF4D4D !important; margin-left: 15px; }
        
        .dark-theme .modal-crop-content { background-color: #1e1e1e; color: #fff; }
        .dark-theme .btn-crop-cancel { background-color: #333; color: #fff; }
        
        .input-delete-pass {
            width: 100%; padding: 10px; margin-top: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .dark-theme .input-delete-pass { background-color: #2a2a2a; color: #fff; border: 1px solid #444; }
    </style>
</head>
<body>
   <div class="main-wrapper">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/img/logos/logoSite.webp" alt="Phronesis" class="logo-light">
                <img src="../assets/img/logos/logoescuro.webp" alt="Phronesis" class="logo-dark">
            </div>
            <nav>
                <p class="nav-title">Conta Pessoal</p>
                <ul>
                    <li><a href="configperfil.php" class="active"><span>👤</span> Meu perfil</a></li>
                    <li>
                        <a href="../perfil/acessibilidade.php" class="nav-link-acessibilidade">
                            <img src="../assets/img/acessibilidades/acessibilidade.webp" alt="Acessibilidade" class="icon-acessibilidade">
                            <span>Acessibilidade</span>
                        </a>
                    </li>
                </ul>

                <?php if (isset($user['tipo']) && $user['tipo'] === 'admin'): ?>
                    <p class="nav-title">Administração</p>
                    <ul>
                        <li><a href="../pages/admin_materias.php"><span>🛠️</span> Painel ADM</a></li>
                    </ul>
                <?php endif; ?>

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
                <img src="../assets/img/banners/meuperfilclaro.webp" alt="Banner" class="banner-img banner-light">
                <img src="../assets/img/banners/escuroperfil.webp" alt="Banner" class="banner-img banner-dark">
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
                        <img src="<?php echo htmlspecialchars($fotoExibicao); ?><?php echo !filter_var($fotoExibicao, FILTER_VALIDATE_URL) ? '?t='.time() : ''; ?>"
                            id="currentAvatar"
                            referrerpolicy="no-referrer"
                            decoding="async"
                            fetchpriority="high"
                            alt="Foto de perfil"
                            onerror="this.onerror=null; this.src='../assets/img/default-user.webp';">
                    </div>
                    <div class="upload-info">
                        <?php if ($isGoogleUser): ?>
                            <h3>Foto de Perfil do Google</h3>
                            <p>Sua foto de perfil é gerenciada e sincronizada diretamente pela sua conta Google.</p>
                        <?php else: ?>
                            <h3>Faça upload da sua foto de perfil</h3>
                            <p>Ajuste sua imagem de exibição para facilitar a identificação.</p>
                            <form id="formFotoPerfil">
                                <input type="file" id="foto_perfil" hidden accept="image/*">
                                <button type="button" id="btnSelectFoto">Selecionar Nova Foto</button>
                                
                                <?php if ($temFotoPersonalizada): ?>
                                    <button type="button" id="btnRemoveFoto" class="btn-remove-foto">Remover Foto</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
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

                <div class="danger-zone" style="margin-top: 30px; padding: 20px; border: 1px solid #FF4D4D; border-radius: 8px;">
                    <h3 style="color: #FF4D4D; margin-top: 0;">Excluir Conta</h3>
                    <p style="font-size: 14px; margin-bottom: 15px;">Excluir sua conta é um caminho sem volta. Todos os seus dados serão apagados permanentemente e você perderá o acesso imediato.</p>
                    <button type="button" id="btnOpenDeleteModal" style="background-color: #FF4D4D; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">EXCLUIR CONTA</button>
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

    <div id="deleteAccountModal" class="modal-crop">
        <div class="modal-crop-content">
            <h3 style="color: #FF4D4D; margin-top: 0;">Confirmar Exclusão</h3>
            
            <p style="font-size: 15px; line-height: 1.5; margin-bottom: 15px;">
                <strong>Atenção:</strong> Esta ação apagará permanentemente todos os seus dados, cronogramas e registros do nosso sistema (Phronesis).
            </p>
            
            <p style="background-color: #FFF3CD; color: #856404; padding: 12px; border-radius: 4px; font-weight: bold; font-size: 14px; border: 1px solid #ffeeba;">
                ⚠️ VOCÊ É O ÚNICO RESPONSÁVEL POR ESTA AÇÃO. Tem certeza absoluta que deseja prosseguir?
            </p>

            <?php if ($isGoogleUser): ?>
                <p style="font-size: 13px; color: #666; margin-top: 10px;">
                    Sua conta foi criada via Google. Seus dados locais serão limpos, mas sua conta Google continuará intocada.
                </p>
                <div class="modal-footer" style="margin-top: 20px;">
                    <button type="button" class="btn-crop-cancel" id="btnCancelDelete">Cancelar</button>
                    <button type="button" class="btn-crop-confirm" id="btnConfirmDeleteGoogle" style="background-color: #FF4D4D; color: white;">Sim, sou responsável e quero excluir</button>
                </div>
            <?php else: ?>
                <div style="margin-top: 15px; text-align: left;">
                    <label for="confirmDeletePassword" style="font-weight: bold; font-size: 14px;">Confirme sua senha atual para prosseguir:</label>
                    <input type="password" id="confirmDeletePassword" class="input-delete-pass" placeholder="Digite sua senha">
                </div>
                <div class="modal-footer" style="margin-top: 20px;">
                    <button type="button" class="btn-crop-cancel" id="btnCancelDelete">Cancelar</button>
                    <button type="button" class="btn-crop-confirm" id="btnConfirmDelete" style="background-color: #FF4D4D; color: white;">Confirmar com Senha e Excluir</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
    <script>
        var isGoogleUser = <?php echo $isGoogleUser ? 'true' : 'false'; ?>;
    </script>
    <script src="../assets/js/lib/configperfil.js" defer></script>

    <?php include '../perfil/vlibras.php'; ?>
</body>
</html>