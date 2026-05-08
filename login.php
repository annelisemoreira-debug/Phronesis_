<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça o seu login no Phronesis!</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: '<?php echo $_SESSION['erro_login']; ?>',
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                            });
                        </script>
                        <?php unset($_SESSION['erro_login']); ?>
                    <?php endif; ?>
                        
                    <?php if(isset($_SESSION['sucesso_login'])): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: '<?php echo $_SESSION['sucesso_login']; ?>',
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                            });
                        </script>
                        <?php unset($_SESSION['sucesso_login']); ?>
                    <?php endif; ?>

                    <form method="POST" action="cad.php">
                        <input type="hidden" name="acao" value="login">
                            
                        <div class="inserirInfo">
                            <label>Email: </label>
                            <input type="email" name="email" placeholder="exemplo@email.com" required>
                        </div>
                            
                        <div class="inserirInfo">
                            <label>Senha: </label>
                            <input type="password" name="senha" placeholder="Digite sua senha" required>
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