<?php
session_start();

// 1. Verifica se a sessão existe
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

$id_usuario = (int) $_SESSION['id'];
$fotoPadraoBanco = 'assets/img/default-user.webp';

// 2. Verifica se o usuário da sessão ainda existe no banco
$stmtUser = $conn->prepare("SELECT nome, email, foto, senha, tipo FROM usuario WHERE id_usuario = ?");
$stmtUser->bind_param("i", $id_usuario);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$isGoogleUser = (isset($_SESSION['login_google']) && $_SESSION['login_google'] === true)
    || filter_var($user['foto'], FILTER_VALIDATE_URL);

/**
 * Apaga do disco a foto antiga (ignora foto padrão e URLs do Google).
 */
function apagarFotoAntiga($foto, $fotoPadraoBanco)
{
    if ($foto && $foto != $fotoPadraoBanco && !filter_var($foto, FILTER_VALIDATE_URL)) {
        $caminho = "../" . $foto;
        if (file_exists($caminho)) {
            @unlink($caminho);
        }
    }
}

// =========================================================================
// 3. PROCESSAMENTO DAS REQUISIÇÕES POST / AJAX
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (ob_get_length()) { ob_clean(); }
    header('Content-Type: application/json');

    $action = $_POST['action'];

    // Usuários do Google não alteram a foto
    if (($action == 'delete_image' || $action == 'crop_image') && $isGoogleUser) {
        echo json_encode(['status' => 'error', 'message' => 'Usuários do Google não podem alterar a foto de perfil.']);
        exit();
    }

    // ---- Remover foto ----
    if ($action == 'delete_image') {
        try {
            apagarFotoAntiga($user['foto'], $fotoPadraoBanco);

            $upd = $conn->prepare("UPDATE usuario SET foto = NULL WHERE id_usuario = ?");
            $upd->bind_param("i", $id_usuario);
            $upd->execute();

            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    // ---- Salvar foto cortada ----
    if ($action == 'crop_image') {
        try {
            $diretorioFisico = "../assets/img/perfis/";
            $diretorioBanco  = "assets/img/perfis/";

            if (!file_exists($diretorioFisico)) {
                mkdir($diretorioFisico, 0755, true);
            }

            $base64_image = $_POST['image_data'] ?? '';

            // Aceita apenas imagem em data URL
            if (!preg_match('/^data:image\/(webp|png|jpeg);base64,/', $base64_image)) {
                echo json_encode(['status' => 'error', 'message' => 'Formato de imagem inválido.']);
                exit();
            }

            $data = explode(',', $base64_image, 2);
            $image_content = base64_decode($data[1], true);

            if ($image_content === false || strlen($image_content) > 5 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'Imagem inválida ou maior que 5 MB.']);
                exit();
            }

            $nomeArquivo = "user_" . $id_usuario . "_" . time() . ".webp";
            $caminhoFisico = $diretorioFisico . $nomeArquivo;
            $caminhoBanco  = $diretorioBanco . $nomeArquivo;

            if (file_put_contents($caminhoFisico, $image_content)) {
                apagarFotoAntiga($user['foto'], $fotoPadraoBanco);

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

    // ---- Excluir conta (com senha) ----
    if ($action == 'delete_account') {
        try {
            $password = $_POST['password'] ?? '';

            if (!password_verify($password, $user['senha'])) {
                echo json_encode(['status' => 'error', 'message' => 'Senha incorreta!']);
                exit();
            }

            apagarFotoAntiga($user['foto'], $fotoPadraoBanco);

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

    // ---- Excluir conta (usuário Google) ----
    if ($action == 'delete_account_google') {
        try {
            if (!$isGoogleUser) {
                echo json_encode(['status' => 'error', 'message' => 'Esta conta exige confirmação por senha.']);
                exit();
            }

            apagarFotoAntiga($user['foto'], $fotoPadraoBanco);

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

    echo json_encode(['status' => 'error', 'message' => 'Ação desconhecida.']);
    exit();
}

// =========================================================================
// 4. DADOS PARA EXIBIÇÃO
// =========================================================================
$caminhoPadrao = "../assets/img/default-user.webp";

function otimizarFotoGoogle($url, $tamanho = 192)
{
    if (strpos($url, 'googleusercontent.com') === false) {
        return $url;
    }
    $url = preg_replace('/=[a-z0-9\-]+$/i', '', $url);
    return $url . '=s' . $tamanho . '-c';
}

if (filter_var($user['foto'], FILTER_VALIDATE_URL)) {
    $fotoExibicao = otimizarFotoGoogle($user['foto']);
    $temFotoPersonalizada = false;
    $fotoEhUrl = true;
} else {
    $temFotoPersonalizada = (!empty($user['foto'])
        && $user['foto'] != $fotoPadraoBanco
        && file_exists("../" . $user['foto']));
    $fotoExibicao = $temFotoPersonalizada ? "../" . $user['foto'] : $caminhoPadrao;
    $fotoEhUrl = false;
}

$ehAdmin = isset($user['tipo']) && ($user['tipo'] === 'admin' || $user['tipo'] === '1' || $user['tipo'] === 1);

$verCss    = @filemtime(__DIR__ . '/../assets/css/configperfil.css') ?: time();
$verJs     = @filemtime(__DIR__ . '/../assets/js/lib/configperfil.js') ?: time();
$verAcessJs = @filemtime(__DIR__ . '/../assets/js/lib/acessibilidade.js') ?: time();
$verBanner = @filemtime(__DIR__ . '/../assets/css/banner.css') ?: time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Phronesis</title>

    <link rel="stylesheet" href="../assets/css/configperfil.css?v=<?php echo $verCss; ?>">
    <link rel="stylesheet" href="../assets/css/banner.css?v=<?php echo $verBanner; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.webp">
    <link rel="preconnect" href="https://lh3.googleusercontent.com">
    <link rel="dns-prefetch" href="https://lh3.googleusercontent.com">

    <!-- Tema, escala de fonte e menu lateral (compartilhado com a página de acessibilidade) -->
    <script src="../assets/js/lib/acessibilidade.js?v=<?php echo $verAcessJs; ?>"></script>
</head>
<body>
    <div class="main-wrapper">

        <!-- Header topo (Celular e Tablet) — mesma logo do header.php -->
        <header class="mobile-header">
            <div class="mobile-logo-container">
                <a href="../index.php"><img src="../assets/img/logos/logoCelularClaro.webp" alt="Phronesis" class="logo-light"></a>
                <a href="../index.php"><img src="../assets/img/logos/logoCelularEscuro.webp" alt="Phronesis" class="logo-dark"></a>
            </div>
            <button type="button" class="mobile-menu-toggle" id="btnOpenSidebar"
                    aria-label="Abrir menu" aria-expanded="false" aria-controls="sidebar">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </header>

        <!-- Fundo escurecido (Overlay) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar / Menu Drawer -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <a href="../index.php"><img src="../assets/img/logos/logoSite.webp" alt="Phronesis" class="logo-light"></a>
                    <a href="../index.php"><img src="../assets/img/logos/logoescuro.webp" alt="Phronesis" class="logo-dark"></a>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-title">Conta Pessoal</p>
                <ul>
                    <li>
                        <a href="configperfil.php" class="active">
                            <span class="nav-icon">👤</span>
                            <span class="nav-text">Meu perfil</span>
                        </a>
                    </li>
                    <li>
                        <a href="acessibilidade.php" class="nav-link-acessibilidade">
                            <img src="../assets/img/acessibilidades/acessibilidade.webp" alt="Acessibilidade" class="icon-acessibilidade" onerror="this.outerHTML='<span>♿</span>'">
                            <span class="nav-text">Acessibilidade</span>
                        </a>
                    </li>
                </ul>

                <!-- Navegação entre páginas -->
                <div class="nav-desktop">
                    <p class="nav-title">Navegação</p>
                    <ul>
                        <li>
                            <a href="../index.php">
                                <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
                                <span class="nav-text">Início</span>
                            </a>
                        </li>
                        <li>
                            <a href="../pages/livros.php">
                                <span class="nav-icon"><i class="fa-solid fa-book"></i></span>
                                <span class="nav-text">Biblioteca</span>
                            </a>
                        </li>
                        <li>
                            <a href="../pages/material.php">
                                <span class="nav-icon"><i class="fa-solid fa-tv"></i></span>
                                <span class="nav-text">Material</span>
                            </a>
                        </li>
                        <li>
                            <a href="../pages/cronograma.php">
                                <span class="nav-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <span class="nav-text">Cronogramas</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if ($ehAdmin): ?>
                    <p class="nav-title">ADMINISTRAÇÃO</p>
                    <ul>
                        <li>
                            <a href="../pages/admin_materias.php">
                                <span class="nav-icon">🛠️</span>
                                <span class="nav-text">Painel ADM</span>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </nav>

            <!-- Direitos autorais -->
            <div class="sidebar-copyright">
                <p>© <?php echo date('Y'); ?> Phronesis_ • Todos os direitos reservados</p>
            </div>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="content">
            <!-- BANNER (claro e escuro · PC / tablet / celular) -->
            <header class="profile-banner">
                <picture class="banner-light">
                    <source media="(max-width: 600px)"  srcset="../assets/img/banners/cellphone/perfilCelularClaro.webp">
                    <source media="(max-width: 1024px)" srcset="../assets/img/banners/tablet/perfilTabletClaro.webp">
                    <img src="../assets/img/banners/pc/claroPerfil.webp" alt="Banner Perfil">
                </picture>
                <picture class="banner-dark">
                    <source media="(max-width: 600px)"  srcset="../assets/img/banners/cellphone/perfilCelularEscuro.webp">
                    <source media="(max-width: 1024px)" srcset="../assets/img/banners/tablet/perfilTableEscuro.webp">
                    <img src="../assets/img/banners/pc/escuroPerfil.webp" alt="Banner Perfil Escuro">
                </picture>
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
                        <img src="<?php echo htmlspecialchars($fotoExibicao); ?><?php echo $fotoEhUrl ? '' : '?t=' . time(); ?>"
                             id="currentAvatar"
                             tabindex="0"
                             role="button"
                             referrerpolicy="no-referrer"
                             decoding="async"
                             fetchpriority="high"
                             alt="Foto de perfil (clique para ampliar)"
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

                <div class="danger-zone">
                    <h3>Excluir Conta</h3>
                    <p>Excluir sua conta é um caminho sem volta. Todos os seus dados serão apagados permanentemente e você perderá o acesso imediato.</p>
                    <button type="button" id="btnOpenDeleteModal" class="btn-delete-open">EXCLUIR CONTA</button>
                </div>

                <div class="footer-actions">
                    <a href="../logout.php">Desconectar</a>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Modal de visualização ampliada da foto -->
    <div id="previewModal" class="modal-preview" aria-hidden="true">
        <span class="close-preview" id="btnClosePreview" title="Fechar" role="button" aria-label="Fechar visualização">&times;</span>

        <div class="preview-stage" id="previewStage">
            <img class="modal-preview-content" id="previewImage" alt="Foto de perfil ampliada" draggable="false" referrerpolicy="no-referrer">
        </div>

        <div class="preview-controls">
            <button type="button" id="btnZoomOut" aria-label="Diminuir zoom">&minus;</button>
            <span id="zoomLevel">100%</span>
            <button type="button" id="btnZoomIn" aria-label="Aumentar zoom">+</button>
            <button type="button" id="btnZoomReset">Redefinir</button>
        </div>
    </div>

    <!-- Modal de Crop da Imagem -->
    <div id="cropModal" class="modal-crop">
        <div class="modal-crop-content">
            <h3>Ajuste sua foto</h3>
            <div class="img-container"><img id="imageToCrop" alt="Imagem para recorte"></div>
            <div class="modal-footer">
                <button type="button" class="btn-crop-cancel" id="btnCancelCrop">Cancelar</button>
                <button type="button" class="btn-crop-confirm" id="btnConfirmCrop">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão de Conta -->
    <div id="deleteAccountModal" class="modal-crop">
        <div class="modal-crop-content">
            <h3 class="delete-title">Confirmar Exclusão</h3>

            <p class="delete-text">
                <strong>Atenção:</strong> Esta ação apagará permanentemente todos os seus dados, cronogramas e registros do nosso sistema (Phronesis).
            </p>

            <p class="delete-warning">
                ⚠️ VOCÊ É O ÚNICO RESPONSÁVEL POR ESTA AÇÃO. Tem certeza absoluta que deseja prosseguir?
            </p>

            <?php if ($isGoogleUser): ?>
                <p class="delete-note">
                    Sua conta foi criada via Google. Seus dados locais serão limpos, mas sua conta Google continuará intocada.
                </p>
                <div class="modal-footer">
                    <button type="button" class="btn-crop-cancel" id="btnCancelDelete">Cancelar</button>
                    <button type="button" class="btn-crop-confirm btn-delete-confirm" id="btnConfirmDeleteGoogle">Sim, sou responsável e quero excluir</button>
                </div>
            <?php else: ?>
                <div class="delete-password-box">
                    <label for="confirmDeletePassword">Confirme sua senha atual para prosseguir:</label>
                    <input type="password" id="confirmDeletePassword" class="input-delete-pass" placeholder="Digite sua senha" autocomplete="current-password">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-crop-cancel" id="btnCancelDelete">Cancelar</button>
                    <button type="button" class="btn-crop-confirm btn-delete-confirm" id="btnConfirmDelete">Confirmar com Senha e Excluir</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="../assets/js/lib/configperfil.js?v=<?php echo $verJs; ?>" defer></script>

    <!-- ================= NAVEGAÇÃO INFERIOR (só celular) ================= -->
    <nav class="bottom-nav" aria-label="Navegação principal">
        <a href="../index.php" class="bn-item">
            <i class="fa-solid fa-house"></i><span>Início</span>
        </a>
        <a href="../pages/livros.php" class="bn-item">
            <i class="fa-solid fa-book"></i><span>Biblioteca</span>
        </a>
        <a href="../pages/material.php" class="bn-item">
            <i class="fa-solid fa-tv"></i><span>Material</span>
        </a>
        <a href="../pages/cronograma.php" class="bn-item">
            <i class="fa-regular fa-calendar-days"></i><span>Cronogramas</span>
        </a>
        <a href="configperfil.php" class="bn-item ativo" aria-current="page">
            <i class="fa-regular fa-user"></i><span>Perfil</span>
        </a>
    </nav>

    <?php include 'vlibras.php'; ?>
</body>
</html>