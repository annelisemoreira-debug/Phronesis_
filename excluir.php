<!DOCTYPE html>
<html lang="en">
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
    <main>
    <div class="mainContainer">
    <h2 class=" mainTitulo, yeseva-one-regular">Excluir Conta</h2>
    <p>Para excluir sua conta, insira seu e-mail e senha.</p>
    <form action="cad.php" method="POST">
        <input type="hidden" name="acao" value="self_delete"> <!-- A ação é definida como self_delete -->
        <div class="inserirInfo">
        <label>E-mail:</label>
        <input type="email" name="email" required><br><br>
        </div>

        <div class="inserirInfo">
        <label>Senha:</label>
        <input type="password" name="senha" required><br><br>
        </div>
        <button class="entrarLogin" type="submit">Excluir Conta</button>
    </form>
    </div>
    </main>
</body>
</html>