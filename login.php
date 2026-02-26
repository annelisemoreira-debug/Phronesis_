<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Faça o seu login no Phronesis!</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <header>
        <div class="logoMarca"><img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis"></div>
        <nav class="menuNav">
            <ul class="menu">
                <li class="sobre"><a href="sobre.php">Sobre</a></li>
                <li class="materiais"><a href="material.php">Materiais</a></li>
                <li class="livros"><a href="livros.php">Livros</a></li>
                <li class="cronogramas"><a href="cronograma.php">Cronogramas</a></li>
                <li class="entrar"><a href="login.php">Entrar</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
        <img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis">
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
    </div>
    </main>
</body>

</html>