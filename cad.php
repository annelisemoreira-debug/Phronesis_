<?php
// Conexão com o banco
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica qual ação foi enviada via POST ou GET (POST para cadastro/login, GET para update/delete)
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// --- AÇÃO: CADASTRAR ---
if (empty($acao)) { 
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    if (empty($email) || empty($senha)) { //Se o email e senha estiverem vazios, não é possivel cadastrar
        die("E-mail e senha são obrigatórios.");
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT); //deixa a senha criptografada / password_default serve para proteção 

    // Ajustei para inserir nome e telefone também, já que estão no form
    $sql = "INSERT INTO cadastro (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
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
elseif ($acao == "login") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT id_cadastro, senha FROM cadastro WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            session_start();
            $_SESSION['id'] = $user['id_cadastro'];
            echo "Login realizado com sucesso! <a href='index.php'>Ir para painel</a>";
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
    $stmt->close();
}


// --- AÇÃO: ATUALIZAR SENHA ---
elseif ($acao == "nova_senha") {
    // Recebe o e-mail enviado pelo formulário via POST
    $email = $_POST['email'];
    // Recebe a nova senha digitada pelo usuário
    $senha = $_POST['senha'];

    // Criptografa a senha usando um método seguro(envolve a transformação de uma senha em texto simples em uma sequência de caracteres de comprimento fixo usando um algoritmo de hash)
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    // Cria o comando SQL para atualizar a senha do usuário pelo e-mail
    $sql = "UPDATE cadastro SET senha=? WHERE email=?";
    // Prepara o comando SQL para evitar ataques (SQL Injection)
    $stmt = $conn->prepare($sql);
     // Substitui os "?" do SQL pelos valores reais
    // "ss" significa: dois valores do tipo string
    $stmt->bind_param("ss", $senhaHash, $email);

    if ($stmt->execute()) {
        echo "Senha atualizada com sucesso! <a href='login.php'>Clique aqui para logar</a>";
    } else {
        echo "Erro ao atualizar.";
    }
    $stmt->close();
}


// --- AÇÃO: ATUALIZAR ---
// Verifica se a ação recebida é "update"
elseif ($acao == "update") {
        // Recebe o ID do usuário (se não existir, usa 0 como padrão)
    $id = $_POST['id_cadastro'] ?? 0;
        // Recebe o e-mail enviado pelo formulário
    $email = $_POST['email'];
    // Recebe a nova senha digitada pelo usuário
    $senha = $_POST['senha'];

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    // Comando SQL para atualizar email e senha pelo ID
    $sql = "UPDATE cadastro SET email=?, senha=? WHERE id_cadastro=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $email, $senhaHash, $id);

    // Executa o comando no banco
    if ($stmt->execute()) {
        echo "Atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar.";
    }
    $stmt->close();
}


// --- AÇÃO: DELETAR ---
elseif ($acao == "delete") {
    $id = $_POST['id_cadastro'];
    $sql = "DELETE FROM cadastro WHERE id_cadastro=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Excluído com sucesso!";
    } else {
        echo "Erro ao excluir.";
    }
    $stmt->close();
}


// --- AÇÃO: AUTO-DELETAR ---
elseif ($acao == "self_delete") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT id_cadastro, senha FROM cadastro WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $sql_del = "DELETE FROM cadastro WHERE id_cadastro=?";
            $stmt_del = $conn->prepare($sql_del);
            $stmt_del->bind_param("i", $user['id_cadastro']);
            if ($stmt_del->execute()) {
                echo "Conta excluída com sucesso! <a href='index.php'>Clique aqui para voltar</a>";
                session_start();
                session_destroy();
            } else {
                echo "Erro ao excluir. <a href='index.php'>Clique aqui para voltar</a>";
            }
            $stmt_del->close();
        } else {
            echo "Senha ou E-mail incorretos! <a href='index.php'>Clique aqui para voltar</a>";
        }
    } else {
        echo "Senha ou E-mail incorretos! <a href='index.php'>Clique aqui para voltar</a>";
    }
    $stmt->close();
} else {
    echo "Acesso inválido! <a href='index.php'>Clique aqui para voltar</a>";
}

$conn->close();
