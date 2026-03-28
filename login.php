<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça o seu login no Phronesis!</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <img src="assets/img/fundoRoxo.png" class="bg-html" alt="Fundo">

    <main>
        <div class="container">
            <div class="glass-card">
                <div class="logo-section">
                    <img src="assets/img/phronesisLogo.png" alt="Logo oficial da Phronesis">
                </div>

                <div class="mainContainer">
                    <h1 class="mainTitulo">Faça seu login!</h1>
                    <p style="color: #031768">Não tem uma conta? <a class="botaoCadastro" href="cadastro.php"> Cadastre-se </a></p>

                    <?php if(isset($_SESSION['erro_login'])): ?>
                        <p class="erroLogin"><?php echo $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?></p>
                    <?php endif; ?>
                        
                    <?php if(isset($_SESSION['sucesso_login'])): ?>
                        <p class="cadastroSucesso"><?php echo $_SESSION['sucesso_login']; unset($_SESSION['sucesso_login']); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="cad.php">
                        <input type="hidden" name="acao" value="login">
                            
                        <div class="inserirInfo">
                            <label>Email: </label>
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
        </div>
    </main>
</body>
</html>