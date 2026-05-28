<?php 
session_start(); 

// Se as variáveis de sessão existirem, o usuário já está logado
if (isset($_SESSION['id']) && isset($_SESSION['nome'])) {
    header("Location: index.php");
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça o seu login no Phronesis!</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        .inserirInfo { position: relative; }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 65%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #031768;
        }
        /* Alinhamento do botão do Google */
        .google-btn-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
    </style>
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
                            <i class="fa-solid fa-eye toggle-password" id="btn-senha"></i>
                        </div>

                        <a class="esqueceuSenha" href="senhaPerdida.php?acao=recuperar" style="text-decoration:none"> Esqueceu sua senha? </a>
                        <button class="entrarLogin" type="submit"> Entrar </button>
                    </form>


                    <div id="g_id_onload"
                        data-client_id="904732005700-hhrsns4i5bl2ll22u57bsr9p0ijgkm4e.apps.googleusercontent.com"
                        data-login_uri="http://localhost/Phronesis_/cad.php"
                        data-auto_prompt="false">
                    </div>

                    <div class="google-btn-container">
                        <div class="g_id_signin"
                             data-type="standard"
                             data-size="large"
                             data-theme="outline"
                             data-text="sign_in_with"
                             data-shape="filled_blue"
                             data-logo_alignment="left"
                             data-width="280">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        const btnSenha = document.getElementById('btn-senha');
        const inputSenha = document.getElementById('senha');

        btnSenha.addEventListener('click', () => {
            if (inputSenha.type === 'password') {
                inputSenha.setAttribute('type', 'text');
                btnSenha.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inputSenha.setAttribute('type', 'password');
                btnSenha.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

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

    <?php include 'perfil/vlibras.php'; ?>
</body>
</html>