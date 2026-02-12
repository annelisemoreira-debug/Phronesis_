<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <main>
        <h1>Faça seu Cadastro!</h1>
        <form method="POST" action="cad.php">
            <hr><br><br>
            <label>Nome: </label> <input type="text" name="nome" required><br>
            <label>E-mail: </label> <input type="email" name="email" required><br>

            <div class="linha_dupla">
                <div class="campo">
                    <label>Senha: </label> <input type="password" name="senha" required><br>
                </div>
                <div class="campo">
                    <label>Telefone: </label> <input type="text" name="telefone"><br>
                </div>
            </div>
            
            <button type="submit"> Cadastrar </button>
            <a href="senhaPerdida.php?acao=recuperar" style="text-decoration:none"> Esqueceu sua senha? </a>
        </form>
  </main>
</body>
</html>