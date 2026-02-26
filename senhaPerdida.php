<?php
// Verifica se o parâmetro de ação existe na URL
if(isset($_GET['acao'])  == "recuperar"){ // Se a acao do GET 
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Conta</title>
    <link rel="stylesheet" href="assets/css/excluir.css">
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
    <div class="mainContainer">
    <h2 class="mainTitulo, yeseva-one-regular">Recuperar Senha</h2>
    <form method="POST" action="cad.php">
        <input type="hidden" name="acao" value="nova_senha">

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Nova senha:</label>
        <input type="password" name="senha" required><br>

        <button  class="entrarLogin" type="submit">Atualizar</button>
    </form>
    </div>
</body>
</html>
<?php

} else {
    echo "Acesso inválido!";
}
?>