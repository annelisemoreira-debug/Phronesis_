<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
</head>
<body>
    <header>
        <div class="logoMarca"><img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis"></div>
        <nav class="menuNav">
            <ul class="menu">
                <li class="sobre"><a href="sobre.php">Sobre</a></li>
                <li class="materiais"><a href="materiais.php">Materiais</a></li>
                <li class="livros"><a href="livros.php">Livros</a></li>
                <li class="cronogramas"><a href="cronogramas.php">Cronogramas</a></li>
                <li class="entrar"><a href="login.php">Entrar</a></li>
            </ul>
        </nav>
    </header>

</body>
</html>
<?php
session_start();

// --- CONFIGURAÇÃO DE CONEXÃO ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Define a ação (POST ou GET)
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// --- AÇÃO: CADASTRAR ---
if (empty($acao)) { 
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    if (empty($email) || empty($senha)) {
        $_SESSION['erro_cad'] = "E-mail e senha são obrigatórios.";
        header("Location: cadastro.php");
        exit();
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // CORREÇÃO: Tabela 'usuario' em vez de 'cadastro'
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

    // CORREÇÃO: id_usuario e tabela usuario
    $sql = "SELECT id_usuario, senha FROM usuario WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id_usuario'];
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

    // CORREÇÃO: Tabela usuario
    $sql = "UPDATE usuario SET senha=? WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $senhaHash, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Senha atualizada com sucesso!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Erro: E-mail não encontrado.'); window.history.back();</script>";
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