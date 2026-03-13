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
session_start();//Inicia a sessão

// Conexão com o banco
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname); //Vai ver se os dados foram conectados

// Se a conexão estiver algum dado inválido, menssagem de erro
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Vai pegar a ação POST e GET (POST {login, cadastro} / GET {atualização, delete})
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';


// --- AÇÃO: CADASTRAR ---
if (empty($acao)) { // Se não estiver pedindo o valor do delete, atualizar e fazer login, é possivel cadastrar no sistema
    $email = $_POST['email'] ?? ''; // Recebe o e-mail enviado pelo formulário via POST
    $senha = $_POST['senha'] ?? ''; // Recebe o senha enviado pelo formulário via POST
    $nome = $_POST['nome'] ?? ''; // Recebe nome enviado pelo formulário via POST
    $telefone = $_POST['telefone'] ?? ''; // Recebe o telefone enviado pelo formulário via POST

    if (empty($email) || empty($senha)) { //Se o email e senha estiverem vazios, não é possivel cadastrar
        $_SESSION['erro_cad'] = "E-mail e senha são obrigatórios.";
        header("Location: cadastro.php");
        exit();
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT); //deixa a senha criptografada / password_default serve para proteção da senha

    $sql = "INSERT INTO cadastro (nome, email, senha, telefone) VALUES (?, ?, ?, ?)"; //vai verificar o banco de dados da tabela cadastro
    $stmt = $conn->prepare($sql); //vai preparar a conexão com o banco
    $stmt->bind_param("ssss", $nome, $email, $senhaHash, $telefone); //vai alterar os tipos de dados para string (ssss) deixando valores reais, seguros

    if ($stmt->execute()) { //vai executar se os dados forem inseridos corretamente
        $_SESSION['sucesso_login'] = "Cadastro realizado com sucesso! Faça seu login.";
        header("Location: login.php");
        exit();
    } else { //Se os dados não forem inseridos corretamente, mostra a menssagem de erro
        $_SESSION['erro_cad'] = "Erro ao cadastrar: " . $conn->error;
        header("Location: cadastro.php");
        exit();
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
            $_SESSION['id'] = $user['id_cadastro']; //Vai procurar o id do usuário para confirmar sua existencia
            header("Location: index.php"); // Redireciona para o painel em caso de sucesso
            exit();
        } else { //se der erro, vai aparecer Senha ou E-mail incorretos
            $_SESSION['erro_login'] = "Senha ou E-mail incorretos!";
            header("Location: login.php");
            exit();
            }
    } else {
        $_SESSION['erro_login'] = "Senha ou E-mail incorretos!";
        header("Location: login.php");
        exit();
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
    } else {
        echo "E-mail inválido. <a href='login.php'>Clique aqui para logar</a>";
    }

    $stmt->close(); //fecha a conexão
    
}


// --- AÇÃO: NOVA SENHA ---
// Mudei para "nova_senha" para bater exatamente com o <input name="acao"> do seu formulário
if ($acao == "nova_senha") {
    
    // Recebe os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Cria o hash da nova senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // SQL: Atualizamos a senha onde o e-mail for igual ao digitado
    $sql = "UPDATE cadastro SET senha=? WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $senhaHash, $email);

    // Executa o comando
    $stmt->execute();

    // VERIFICAÇÃO DE SUCESSO REAL:
    // Se affected_rows for maior que 0, significa que o e-mail existia e foi alterado.
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Senha atualizada com sucesso!'); window.location.href='login.php';</script>";
    } else {
        // Se cair aqui, o comando rodou, mas o e-mail não foi encontrado no banco de dados.
        echo "<script>alert('Erro: O e-mail informado não existe em nosso sistema.'); window.history.back();</script>";
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
                session_start(); // Inicia a sessão para poder destruí-la
            } else {
                echo "Erro ao excluir. <a href='index.php'>Clique aqui para voltar</a>";
            }
            $stmt_del->close(); // Fecha a conexão do DELETE
        } else {
            echo "Senha ou E-mail incorretos! <a href='index.php'>Clique aqui para voltar</a>";
        }
    
    $stmt->close(); //fecha a conexão do SELECT
} else {
    echo "Senha ou E-mail incorretos! <a href='index.php'>Clique aqui para voltar</a>";
}
}

$conn->close();
