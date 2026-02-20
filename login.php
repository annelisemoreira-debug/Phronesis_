<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <h1>Faça seu login!</h1>
        <p>Não tem uma conta? <a href="cadastro.php" style="text-decoration:none"> Cadastre-se </a></p>

        <form method="POST" action="cad.php">
            <input type="hidden" name="acao" value="login">

            <hr><br><br>
            <label>E-mail: </label> <input type="email" name="email" required><br>
            <label>Senha: </label> <input type="password" name="senha" required><br>

            <button type="submit"> Entrar </button>
            <a href="senhaPerdida.php?acao=recuperar" style="text-decoration:none"> Esqueceu sua senha? </a>
        </form>
    </main>
</body>

</html>