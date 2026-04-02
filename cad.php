<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/cad.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <img src="assets/img/fundoAmarelo01.png" class="bg-html" alt="Fundo">
</body>
</html>

<?php
session_start();

// --- CONFIGURAÇÃO DE CONEXÃO ---
// senha para conexão: Home@spSENAI2025!
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
// AJUSTE AQUI: Verificamos se a ação é 'cadastro'
if ($acao == "cadastro") { 
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
        echo "<script>
            Swal.fire({
                title: 'Sucesso!',
                text: 'A sua senha foi alterada',
                icon: 'success',
                confirmButtonColor: '#001a8b'
            }).then(() => {
                window.location.href='login.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Erro!',
                text: 'E-mail não encontrado.',
                icon: 'error',
                confirmButtonColor: '#001a8b'
            }).then(() => {
                window.history.back();
            });
        </script>";
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