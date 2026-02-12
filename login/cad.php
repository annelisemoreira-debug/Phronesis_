<?php
// Conexão com o banco
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica qual ação foi enviada via POST ou GET
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// --- AÇÃO: CADASTRAR ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($acao)) {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    if (empty($email) || empty($senha)) {
        die("E-mail e senha são obrigatórios.");
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Ajustei para inserir nome e telefone também, já que estão no form
    $sql = "INSERT INTO usuario (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senhaHash, $telefone);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso! <a href='login.php'>Clique aqui para logar</a>";
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }
    $stmt->close();
}
// --- AÇÃO: LOGAR ---
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $acao == "login") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT id_usuario, senha FROM usuario WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            session_start();
            $_SESSION['id'] = $user['id_usuario'];
            echo "Login realizado com sucesso! <a href='painel.php'>Ir para painel</a>";
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
    $stmt->close();
}
// --- AÇÃO: ATUALIZAR SENHA ---
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $acao == "nova_senha") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $sql = "UPDATE usuario SET senha=? WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $senhaHash, $email);

    if ($stmt->execute()) {
        echo "Senha atualizada com sucesso! <a href='login.php'>Clique aqui para logar</a>";
    } else {
        echo "Erro ao atualizar.";
    }
    $stmt->close();
}
else {
    echo "Acesso inválido!";
}

$conn->close();
?>