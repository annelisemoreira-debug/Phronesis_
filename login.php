<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Faça o seu login no Phronesis!</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <main>
       <div class="mainContainer">
            <h1 class="mainTitulo, yeseva-one-regular">Faça seu login!</h1>
            <p>Não tem uma conta? <a class="botaoCadastro" href="cadastro.php" style="text-decoration:none"> Cadastre-se </a></p><br>

            <form method="POST" action="cad.php">
                <input type="hidden" name="acao" value="login">

                <div class="inserirInfo">
                    <label>E-mail: </label>
                    <input type="email" name="email" required>
                </div>
                
               <div class="inserirInfo">
                    <label>Senha: </label>
                    <input type="password" name="senha" required>
               </div>

                <a class="esqueceuSenha" href="senhaPerdida.php?acao=recuperar" style="text-decoration:none"> Esqueceu sua senha? </a>
                <button class="entrarLogin" type="submit"> Entrar </button>
            </form>
       </div>
    </main>
</body>

</html>