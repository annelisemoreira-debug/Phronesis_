<?php

$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}


if(isset($_GET['acao']) && $_GET['acao'] == "recuperar"){

    ?>

    <h2>Recuperar Senha</h2>

    <form method="POST" action="cad.php">

        <input type="hidden" name="acao" value="nova_senha">

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Nova senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Atualizar</button>

    </form>

    <?php
    exit;
}


if(isset($_POST['acao']) && $_POST['acao'] == "nova_senha"){

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuario SET senha=? WHERE email=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $senhaHash, $email);

    if($stmt->execute()){
        echo "Senha atualizada com sucesso!";
    }else{
        echo "Erro ao atualizar.";
    }

    exit;
}


if(isset($_POST['email']) && isset($_POST['senha'])){

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuario WHERE email=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $user = $result->fetch_assoc();

        if(password_verify($senha, $user['senha'])){
            echo "Login realizado com sucesso!";
        }else{
            echo "Senha incorreta!";
        }

    }else{
        echo "Usuário não encontrado!";
    }

    exit;
}

echo "Acesso inválido!";

?>

