<?php

if(isset($_POST['email']) && isset($_POST['senha'])){

    $email = $_POST['email'];
    $senha = $_POST['senha'];

}else{
    echo "Acesso inválido!";
    exit;
}

try{

    $servername = "localhost";
    $username = "root";
    $password = "Home@spSENAI2025!";
    $dbname = "phronesis";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    echo "Conectado com sucesso! <br>";

    $sql = "INSERT INTO usuario (email, senha) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ss", $email, $senha);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro ao cadastrar.";
    }

    $stmt->close();
    $conn->close();

}catch(mysqli_sql_exception $erro){
    echo "Erro no banco de dados.";
}

?>
