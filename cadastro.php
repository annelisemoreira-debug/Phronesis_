<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
</head>
<body>
    <header>
        <div class="logoMarca"><img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis"></div>
        <nav class="menuNav">
            <ul class="menu">
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
                <h1 class="mainTitulo yeseva-one-regular">Faça seu cadastro!</h1>

                <?php if(isset($_SESSION['erro_cad'])): ?>
                    <p style="color: #ff4d4d; font-weight: bold;"><?php echo $_SESSION['erro_cad']; unset($_SESSION['erro_cad']); ?></p>
                <?php endif; ?>

                <form method="POST" action="cad.php">
                    <div class="inserirInfo">
                        <label>Nome: </label> <input type="text" name="nome" required>
                    </div>

                    <div class="inserirInfo">
                        <label>Senha: </label> <input type="password" name="senha" required>
                    </div>
                
                    <div class="linha_dupla">
                        <div class="inserirInfo">
                            <div class="campo">
                                <label>E-mail: </label> <input type="email" name="email" required><br>
                            </div>
                        </div>

                        <div class="inserirInfo">
                            <div class="campo">
                                <label>Telefone: </label> <input type="text" name="telefone" required><br>
                            </div>
                        </div>
                    </div>

                    <button class="entrarLogin" type="submit"> Cadastrar </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
