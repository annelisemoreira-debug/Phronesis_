<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça parte do Phronesis!</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
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
                    <h1 class="mainTitulo">Faça seu cadastro!</h1>
                    <p style="color: #031768">Já possui uma conta? <a class="botaoLogin" href="login.php"> Entrar </a></p>

                    <?php if(isset($_SESSION['erro_cad'])): ?>
                        <p style="color: #ff4d4d; font-weight: bold; font-family: Arial, Helvetica, sans-serif"><?php echo $_SESSION['erro_cad']; unset($_SESSION['erro_cad']); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="cad.php">
                        <input type="hidden" name="acao" value="cadastro">

                        <div class="inserirInfo">
                            <label>Nome: </label> 
                            <input type="text" name="nome" required>
                        </div>

                        <div class="inserirInfo">
                            <label>E-mail: </label> 
                            <input type="email" name="email" required>
                        </div>
                    
                        <div class="linha_dupla">
                            <div class="inserirInfo">
                                <label>Senha: </label> 
                                <input type="password" name="senha" required>
                            </div>

                            <div class="inserirInfo">
                                <label>Telefone: </label> 
                                <input type="text" name="telefone" required>
                            </div>
                        </div>

                        <button class="entrarLogin" type="submit"> Cadastrar </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>