<?php
session_start();

include __DIR__ . '/conexao.php';

// Captura a ação padrão vinda dos formulários tradicionais
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// CAPTURA AUTOMÁTICA DO GOOGLE:
// Se o Google enviar uma credencial via POST, força a ação para "google_login"
if (isset($_POST['credential'])) {
    $acao = "google_login";
}

// --- AÇÃO: CADASTRAR ---
if ($acao == "cadastro") { 
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    // Validação básica de campos vazios
    if (empty($email) || empty($senha)) {
        $_SESSION['status_cadastro'] = "erro_geral"; // Alinhado com o SweetAlert
        header("Location: cadastro.php");
        exit();
    }

    // --- NOVA VALIDAÇÃO: VERIFICAR SE O E-MAIL JÁ ESTÁ CADASTRADO ---
    $sqlCheck = "SELECT id_usuario FROM usuario WHERE email = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // Encontrou um usuário com o mesmo e-mail! dispara o alerta do SweetAlert personalizado
        $_SESSION['status_cadastro'] = "email_duplicado";
        header("Location: cadastro.php");
        exit();
    }
    $stmtCheck->close();
    // -----------------------------------------------------------------

    // --- NOVA VALIDAÇÃO DE FORÇA DE SENHA ---
    // Regra: Mínimo 6 caracteres, 1 letra, 1 número e 1 caractere especial
    $regex = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&.])[A-Za-z\d@$!%*#?&.]{6,}$/";

    if (!preg_match($regex, $senha)) {
        $_SESSION['status_cadastro'] = "senha_incorreta"; // Alinhado com o SweetAlert
        header("Location: cadastro.php");
        exit();
    }
    // ----------------------------------------

// --- FINAL DO BLOCO DE CADASTRO NO CAD.PHP ---
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuario (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senhaHash, $telefone);

    if ($stmt->execute()) {
        // SALVA O SUCESSO E REDIRECIONA DIRETO PARA O LOGIN!
        $_SESSION['sucesso_login'] = "Cadastro realizado com sucesso! Faça seu login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['status_cadastro'] = "erro_geral";
        header("Location: cadastro.php");
        exit();
    }
}

// --- AÇÃO: LOGAR ---
elseif ($acao == "login") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Alterado para buscar também o nome do usuário
    $sql = "SELECT id_usuario, nome, senha FROM usuario WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id_usuario'];
            $_SESSION['nome'] = $user['nome']; // Armazena o nome na sessão para usar no index
            header("Location: index.php");
            exit();
        }
    }
    
    $_SESSION['erro_login'] = "Senha ou E-mail incorretos!";
    header("Location: login.php");
    exit();
}

// --- AÇÃO: ATUALIZAR SENHA (UNIFICADA) ---
elseif ($acao == "nova_senha") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuario SET senha=? WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $senhaHash, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['sucesso_login'] = "Senha alterada com sucesso!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['erro_recuperacao'] = "E-mail não encontrado ou senha igual à anterior.";
        header("Location: senhaPerdida.php?acao=recuperar");
        exit();
    }
}

// --- AÇÃO: LOGAR OU CADASTRAR VIA GOOGLE IDENTITY SERVICES ---
elseif ($acao == "google_login") {
    // O Google envia o JWT no campo POST nomeado 'credential'
    $id_token = $_POST['credential'] ?? '';

    // Detecta dinamicamente a página de origem para saber para onde retornar em caso de erro
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $origemRetorno = (strpos($referer, 'cadastro.php') !== false) ? 'cadastro.php' : 'login.php';
    $sessionErroKey = ($origemRetorno === 'cadastro.php') ? 'erro_cad' : 'erro_login';

    if (empty($id_token)) {
        $_SESSION[$sessionErroKey] = "Não foi possível validar as credenciais do Google.";
        header("Location: " . $origemRetorno);
        exit();
    }

    // Validação segura requisitando diretamente o endpoint de conferência da API do Google
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);
    $response = @file_get_contents($url);
    
    if ($response === FALSE) {
        $_SESSION[$sessionErroKey] = "Falha de comunicação com os servidores de autenticação do Google.";
        header("Location: " . $origemRetorno);
        exit();
    }

    $payload = json_decode($response, true);

    // Se a assinatura do Token for válida e trouxer o e-mail verificado do usuário
    if (isset($payload['email'])) {
        $email = $payload['email'];
        $nome = $payload['name'] ?? 'Usuário Google';
        $foto_google = $payload['picture'] ?? 'assets/img/default-user.webp'; // CAPTURA DA FOTO AQUI
        
        // Verifica se este e-mail do Google já está na nossa tabela local 'usuario'
        $sql = "SELECT id_usuario, nome FROM usuario WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Cenário 1: Usuário já existe -> Realiza o login direto na sessão e atualiza a foto se mudou
            $user = $result->fetch_assoc();
            
            // Atualiza a URL da foto vinda do Google no banco de dados para sincronizar
            $sql_upd_foto = "UPDATE usuario SET foto=? WHERE id_usuario=?";
            $stmt_upd_foto = $conn->prepare($sql_upd_foto);
            $stmt_upd_foto->bind_param("si", $foto_google, $user['id_usuario']);
            $stmt_upd_foto->execute();

            $_SESSION['id'] = $user['id_usuario'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['login_google'] = true; // Define que o usuário veio pelo Google
            header("Location: index.php");
            exit();
        } else {
            // Cenário 2: Usuário não existe -> Cria o registro de forma automática salvando a foto e depois loga
            $senhaProvisoria = password_hash(uniqid(rand(), true), PASSWORD_DEFAULT);
            $telefoneNulo = "";

            // Substituído o valor padrão de cadastro pela variável contendo a foto do Google
            $sql_cad = "INSERT INTO usuario (nome, email, senha, telefone, foto) VALUES (?, ?, ?, ?, ?)";
            $stmt_cad = $conn->prepare($sql_cad);
            $stmt_cad->bind_param("sssss", $nome, $email, $senhaProvisoria, $telefoneNulo, $foto_google);
            
            if ($stmt_cad->execute()) {
                $_SESSION['id'] = $stmt_cad->insert_id;
                $_SESSION['nome'] = $nome;
                $_SESSION['login_google'] = true; // Define que o usuário veio pelo Google
                header("Location: index.php");
                exit();
            } else {
                $_SESSION[$sessionErroKey] = "Erro na gravação do cadastro automático.";
                header("Location: " . $origemRetorno);
                exit();
            }
        }
    } else {
        $_SESSION[$sessionErroKey] = "Token do Google inválido ou expirado.";
        header("Location: " . $origemRetorno);
        exit();
    }
}

// --- AÇÃO: AUTO-DELETAR (SELF DELETE) ---
elseif ($acao == "self_delete") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $sql = "SELECT id_usuario, senha FROM usuario WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $sql_del = "DELETE FROM usuario WHERE id_usuario=?";
            $stmt_del = $conn->prepare($sql_del);
            $stmt_del->bind_param("i", $user['id_usuario']);
            
            if ($stmt_del->execute()) {
                session_destroy();
                echo "<script>alert('Conta excluída!'); window.location.href='index.php';</script>";
                exit();
            }
        }
    }
    echo "<script>alert('Dados incorretos!'); window.history.back();</script>";
}

$conn->close();
?>