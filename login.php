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
    <img src="assets/img/fundoRoxo.webp" class="bg-html" alt="Fundo">

    <main>
        <div class="container">
            <div class="glass-card">
                <div class="logo-section">
                    <img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis">
                </div>

                <div class="mainContainer">
                    <h1 class="mainTitulo">Faça seu login!</h1>
                    <p style="color: #031768">Não tem uma conta? <a class="botaoCadastro" href="cadastro.php"> Cadastre-se </a></p>

                    <form id="formLogin" method="POST" action="cad.php" novalidate>
                        <input type="hidden" name="acao" value="login">
                            
                        <div class="inserirInfo">
                            <label>Email: </label>
                            <input type="email" name="email" id="email" placeholder="exemplo@email.com" required>
                        </div>
                            
                        <div class="inserirInfo">
                            <label>Senha: </label>
                            <input type="password" name="senha" id="senha" placeholder="Digite sua senha" required>
                        </div>

                        <a class="esqueceuSenha" href="senhaPerdida.php?acao=recuperar" style="text-decoration:none"> Esqueceu sua senha? </a>
                        <button class="entrarLogin" type="submit"> Entrar </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Configuração padrão do Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

        // 1. Captura mensagens vindas do PHP (Login incorreto ou Sucesso no cadastro)
        <?php if(isset($_SESSION['erro_login'])): ?>
            Toast.fire({
                icon: 'error',
                title: '<?php echo $_SESSION['erro_login']; ?>'
            });
        <?php unset($_SESSION['erro_login']); endif; ?>

        <?php if(isset($_SESSION['sucesso_login'])): ?>
            Toast.fire({
                icon: 'success',
                title: '<?php echo $_SESSION['sucesso_login']; ?>'
            });
        <?php unset($_SESSION['sucesso_login']); endif; ?>

        // 2. Validação Manual para evitar os balões padrão do navegador
        document.getElementById('formLogin').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!email || !senha) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Por favor, preencha todos os campos."
                });
            } else if (!emailRegex.test(email)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Formato de e-mail inválido."
                });
            }
        });
    </script>
</body>
</html>