<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


  <main>
    

            <h1>Faça seu Cadastro!</h1>

        <form method="POST" action="cad.php">
            <hr><br><br>

            <label>Nome: </label> <input type="text" name="nome" id="nome" placeholder="Insira o seu nome"><br>

             <label>E-mail: </label> <input type="email" name="email" id="email" placeholder="Insira o seu email"><br>

            <div class="linha_dupla">
                <div class="campo">
                <label>Senha: </label> <input type="text" name="senha" id="senha" placeholder="Insira o seu senha"><br>
            </div>
                <div class="campo">
            <label>telefone: </label> <input type="text" name="senha" id="senha" placeholder="Insira o seu senha"><br>
                </div>
            </div>
            

            <button type="submit" name="button"> Enviar </button>

            <a rel="stylesheet" href="senhaPerdida.php"  text-decoration="none"> Esqueceu sua senha? </a>
        </form>
    
</div>
</section>
  </main>



</body>
</html>