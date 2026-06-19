<?php
session_start();

include __DIR__ . '/conexao.php';

// Captura a ação padrão vinda dos formulários tradicionais
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// CAPTURA AUTOMÁTICA DO GOOGLE:
if (isset($_POST['credential'])) {
    $acao = "google_login";
}

// --- AÇÃO: CADASTRAR ---
if ($acao == "cadastro") { 
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    if (empty($email) || empty($senha)) {
        $_SESSION['status_cadastro'] = "erro_geral"; 
        header("Location: cadastro.php");
        exit();
    }

    $sqlCheck = "SELECT id_usuario FROM usuario WHERE email = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $_SESSION['status_cadastro'] = "email_duplicado";
        header("Location: cadastro.php");
        exit();
    }
    $stmtCheck->close();

    $regex = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&.])[A-Za-z\d@$!%*#?&.]{6,}$/";
    if (!preg_match($regex, $senha)) {
        $_SESSION['status_cadastro'] = "senha_incorreta"; 
        header("Location: cadastro.php");
        exit();
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuario (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senhaHash, $telefone);

    if ($stmt->execute()) {
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

    $sql = "SELECT id_usuario, nome, senha FROM usuario WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id_usuario'];
            $_SESSION['nome'] = $user['nome']; 
            $_SESSION['email'] = $email; // Salva o e-mail para validações de segurança posteriores
            header("Location: index.php");
            exit();
        }
    }
    
    $_SESSION['erro_login'] = "Senha ou E-mail incorretos!";
    header("Location: login.php");
    exit();
}

// --- AÇÃO: ATUALIZAR SENHA (CORRIGIDA CONTRA SEQUESTRO DE CONTAS) ---
elseif ($acao == "nova_senha") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // PROTEÇÃO: O usuário precisa estar logado E o e-mail a ser alterado deve pertencer à sessão dele
    if (!isset($_SESSION['id']) || !isset($_SESSION['email']) || $_SESSION['email'] !== $email) {
        $_SESSION['erro_recuperacao'] = "Ação não autorizada.";
        header("Location: senhaPerdida.php?acao=recuperar");
        exit();
    }

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
        $_SESSION['erro_recuperacao'] = "Nova senha não pode ser igual à anterior.";
        header("Location: senhaPerdida.php?acao=recuperar");
        exit();
    }
}

// --- AÇÃO: GOOGLE LOGIN ---
elseif ($acao == "google_login") {
    $id_token = $_POST['credential'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $origemRetorno = (strpos($referer, 'cadastro.php') !== false) ? 'cadastro.php' : 'login.php';
    $sessionErroKey = ($origemRetorno === 'cadastro.php') ? 'erro_cad' : 'erro_login';

    if (empty($id_token)) {
        $_SESSION[$sessionErroKey] = "Não foi possível validar as credenciais do Google.";
        header("Location: " . $origemRetorno);
        exit();
    }

    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);
    $response = @file_get_contents($url);
    
    if ($response === FALSE) {
        $_SESSION[$sessionErroKey] = "Falha de comunicação com os servidores de autenticação do Google.";
        header("Location: " . $origemRetorno);
        exit();
    }

    $payload = json_decode($response, true);

    if (isset($payload['email'])) {
        $email = $payload['email'];
        $nome = $payload['name'] ?? 'Usuário Google';
        $foto_google = $payload['picture'] ?? 'assets/img/default-user.webp'; 
        
        $sql = "SELECT id_usuario, nome FROM usuario WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            $sql_upd_foto = "UPDATE usuario SET foto=? WHERE id_usuario=?";
            $stmt_upd_foto = $conn->prepare($sql_upd_foto);
            $stmt_upd_foto->bind_param("si", $foto_google, $user['id_usuario']);
            $stmt_upd_foto->execute();

            $_SESSION['id'] = $user['id_usuario'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['email'] = $email;
            $_SESSION['login_google'] = true; 
            header("Location: index.php");
            exit();
        } else {
            $senhaProvisoria = password_hash(uniqid(rand(), true), PASSWORD_DEFAULT);
            $telefoneNulo = "";

            $sql_cad = "INSERT INTO usuario (nome, email, senha, telefone, foto) VALUES (?, ?, ?, ?, ?)";
            $stmt_cad = $conn->prepare($sql_cad);
            $stmt_cad->bind_param("sssss", $nome, $email, $senhaProvisoria, $telefoneNulo, $foto_google);
            
            if ($stmt_cad->execute()) {
                $_SESSION['id'] = $stmt_cad->insert_id;
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                $_SESSION['login_google'] = true; 
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

// --- AÇÃO: AUTO-DELETAR ---
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