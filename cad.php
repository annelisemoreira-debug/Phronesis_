<?php
session_start();

// --- CONFIGURAÇÃO DE CONEXÃO ---
// --- SENHA PARA CONEXÃO: Home@spSENAI2025! ---

$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// --- AÇÃO: CADASTRAR ---
if ($acao == "cadastro") { 
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    // Validação básica de campos vazios
    if (empty($email) || empty($senha)) {
        $_SESSION['erro_cad'] = "E-mail e senha são obrigatórios.";
        header("Location: cadastro.php");
        exit();
    }

    // --- NOVA VALIDAÇÃO DE FORÇA DE SENHA ---
    // Regra: Mínimo 6 caracteres, 1 letra, 1 número e 1 caractere especial
    $regex = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/";

    if (!preg_match($regex, $senha)) {
        $_SESSION['erro_cad'] = "A senha não atende aos requisitos: mínimo de 6 dígitos, uma letra, um número e um símbolo.";
        header("Location: cadastro.php");
        exit();
    }
    // ----------------------------------------

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuario (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senhaHash, $telefone);

    if ($stmt->execute()) {
        $_SESSION['sucesso_login'] = "Cadastro realizado com sucesso! Faça seu login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['erro_cad'] = "Erro ao cadastrar: " . $conn->error;
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
        // Sucesso: Mantemos o modal de confirmação ou usamos Toast se preferir
        // Para consistência com seu pedido de aviso flutuante:
        $_SESSION['sucesso_login'] = "Senha alterada com sucesso!";
        header("Location: login.php");
        exit();
    } else {
        // ERRO: Aqui é onde estava aquela tela branca com o modal
        // Agora salvamos na sessão e voltamos para a página anterior
        $_SESSION['erro_recuperacao'] = "E-mail não encontrado ou senha igual à anterior.";
        header("Location: senhaPerdida.php?acao=recuperar");
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