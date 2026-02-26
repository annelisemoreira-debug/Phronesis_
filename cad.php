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

// Vai pegar a ação POST e GET (POST {login, cadastro} / GET {atualização, delete})
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// --- AÇÃO: CADASTRAR ---
if (empty($acao)) { // Se o login, atulização e delete estiverem vazios, é possivel cadastrar no sistema
    $email = $_POST['email'] ?? ''; // Recebe o e-mail enviado pelo formulário via POST
    $senha = $_POST['senha'] ?? ''; // Recebe o senha enviado pelo formulário via POST
    $nome = $_POST['nome'] ?? ''; // Recebe nome enviado pelo formulário via POST
    $telefone = $_POST['telefone'] ?? ''; // Recebe o telefone enviado pelo formulário via POST

    if (empty($email) || empty($senha)) { //Se o email e senha estiverem vazios, não é possivel cadastrar
        die("E-mail e senha são obrigatórios.");
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT); //deixa a senha criptografada / password_default serve para proteção da senha

    // Ajustei para inserir nome e telefone também, já que estão no form
    $sql = "INSERT INTO cadastro (nome, email, senha, telefone) VALUES (?, ?, ?, ?)"; //vai verificar o banco de dados da tabela cadastro
    $stmt = $conn->prepare($sql); //vai preparar a conexão com o banco
    $stmt->bind_param("ssss", $nome, $email, $senhaHash, $telefone); //vai alterar os tipos de dados para string (ssss) deixando valores reais, seguros

    if ($stmt->execute()) { //vai executar se os dados forem inseridos corretamente
        echo "Cadastro realizado com sucesso! <a href='login.php'>Clique aqui para logar</a>";

    } else { //Se os dados não forem inseridos corretamente, mostra a menssagem de erro
        echo "Erro ao cadastrar: " . $conn->error;
    }


    $stmt->close(); //fecha a conexão
}


// --- AÇÃO: LOGAR ---
elseif ($acao == "login") { //vai verificar se a ação foi preenchida com login, se sim, é possivel logar no sistema
    $email = $_POST['email']; // Recebe o e-mail enviado pelo formulário via POST
    $senha = $_POST['senha']; // Recebe o senha enviado pelo formulário via POST

    $sql = "SELECT id_cadastro, senha FROM cadastro WHERE email=?"; // Vai selecionar o id_cadastro e senha para confirmar se realmente existem
    $stmt = $conn->prepare($sql); //prepara a conexão com o banco
    $stmt->bind_param("s", $email); //altera o valor do email para STRING, deixando o valor real, seguro
    $stmt->execute(); //vai executar a consulta no banco
    $result = $stmt->get_result(); //vai pegar o valor da consulta se realmente existir no banco 

    if ($result->num_rows == 1) { //vai verificar se realmente existe a consulta, se for 1 (verdadeiro), se for 0 (falso), mostrando uma menssagem de usuário não encontrado
        $user = $result->fetch_assoc(); //vai pegar o valor da consulta 
        if (password_verify($senha, $user['senha'])) { //vai transformar a senha em hash e vai comparar a senha com a senha cadastrada
            session_start(); //Vai buscar manter a conexão com o banco
            $_SESSION['id'] = $user['id_cadastro']; //Vai procurar o id do usuário para confirmar sua existencia
            echo "Login realizado com sucesso! <a href='index.php'>Ir para painel</a>";
        } else { //se der erro, vai aparecer Senha ou E-mail incorretos
            echo "Senha ou E-mail incorretos! <a href='login.php'>Clique aqui para tentar novamente</a>";
        }
    }

    $stmt->close(); //fecha a conexão

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

    if ($stmt->execute()) { // Vai executar o comando no banco para ver se o email existe
        echo "Senha atualizada com sucesso! <a href='login.php'>Clique aqui para logar</a>";
    } else { //se não, menssagem de erro
        echo "E-mail inválido.";
    }
    $stmt->close(); //fecha a conexão
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
     // Recebe o ID do usuário enviado pelo formulário
    $id = $_POST['id_cadastro'];
     // Comando SQL para apagar o usuário pelo ID
    $sql = "DELETE FROM cadastro WHERE id_cadastro=?";
    $stmt = $conn->prepare($sql);
    // Substitui o ? pelo valor do ID (i = inteiro)
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Excluído com sucesso!";
    } else {
        echo "Erro ao excluir.";
    }
    $stmt->close();
}


// --- AÇÃO: AUTO-DELETAR ---
// Verifica se a ação é "self_delete"
elseif ($acao == "self_delete") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

     // Busca o usuário no banco pelo e-mail
    $sql = "SELECT id_cadastro, senha FROM cadastro WHERE email=?";
    $stmt = $conn->prepare($sql);
    // Substitui o ? pelo e-mail
    $stmt->bind_param("s", $email);
    // Executa a consulta
    $stmt->execute();
    // Guarda o resultado
    $result = $stmt->get_result();

    // Verifica se encontrou exatamente 1 usuário
    if ($result->num_rows == 1) {
        // Pega os dados do usuário
        $user = $result->fetch_assoc();
        // Confere se a senha digitada é igual à do banco
        if (password_verify($senha, $user['senha'])) {
            // Se a senha estiver correta, prepara o DELETE
            $sql_del = "DELETE FROM cadastro WHERE id_cadastro=?";
            $stmt_del = $conn->prepare($sql_del);
            // Substitui o ? pelo ID do usuário
            $stmt_del->bind_param("i", $user['id_cadastro']);
            if ($stmt_del->execute()) {
                echo "Conta excluída com sucesso! <a href='index.php'>Clique aqui para voltar</a>";
                 // Encerra a sessão do usuário
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
