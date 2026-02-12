<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">

    <script>
        <?php
include('conexao.php');
session_start();

if(isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuario WHERE email = '$email'";
    $query = $conn->query($sql);

    if($query->num_rows > 0) {
        $usuario = $query->fetch_assoc();
        
        // Verifica a senha criptografada
        if(password_verify($senha, $usuario['senha'])) {
            $_SESSION['id'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            
            header("Location: painel.php"); // Redireciona para a área logada
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "E-mail não encontrado!";
    }
}
?>
    </script>
</head>
<body>


  <main>
    

            <h1>Faça seu login!</h1>
            <p>Não tem uma conta? <a rel="stylesheet" href="cadastro.php"  text-decoration="none"> Cadastre-se </a></p>


        <form method="POST" action="cad.php">
            <hr><br><br>

            <div class="linha_dupla">
                <div class="campo">
                <label>E-mail: </label> <input type="email" name="email" id="email" placeholder="Insira o seu email"><br>
            </div>
                <div class="campo">
            <label>Senha: </label> <input type="text" name="senha" id="senha" placeholder="Insira o seu senha"><br>
                </div>
            </div>
            

            <button type="submit" name="button"> Enviar </button>

            <a rel="stylesheet" href="index.html"  text-decoration="none"> Esqueceu sua senha? </a>
        </form>
    
</div>
</section>
  </main>



</body>
</html>