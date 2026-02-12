<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h1> CADASTRO </h1>
    
</body>
</html>

<?php

// Isso impede o PHP de tentar ler o POST se o formulário não foi enviado

    $email = $_POST['email'];
    $senha = $_POST['senha'];

   echo "<br>email: ".$email;
   echo "<br>senha: ".$senha;


   try{

        //conexão com banco de dados

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "phronesis";

        // Cria a conexão

        $conn = new mysqli($servername, $username, $password,$dbname);

    
        if ($conn->connect_error) {
            die("<br>Erro na conexão com o banco de dados: " . $conn->connect_error);

            }
        else{

                echo "<br>OK! Conexão estabelecida com banco de dados.";

                 // SQL de inserção
                $sql = "INSERT INTO usuario (email, senha) VALUES (?, ?)";


                // Prepara a query
                $stmt = $conn->prepare($sql);


                // Associa os parâmetros

                $stmt->bind_param("ss", $email, $senha);
                // s = string | d = double (decimal)


                // Executa

                if ($stmt->execute()) {
                    echo "<br>Funcionário cadastrado com sucesso!";

                } else {
                    echo "Erro ao cadastrar funcionário.";
                }


                $stmt->close();
                $conn->close();

               

         }

   }catch(mysqli_sql_exception $erro){
       echo "<br>Erro de acesso ao Banco de dados";
   }

 

?>