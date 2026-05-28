<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça parte do Phronesis!</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>
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
                    <h1 class="mainTitulo">Faça seu cadastro!</h1>
                    <p style="color: #031768; margin-bottom: 2px;">Já possui uma conta? <a class="botaoLogin" href="login.php"> Entrar </a></p>

                    <form id="formCadastro" method="POST" action="cad.php" novalidate style="display: flex; flex-direction: column; gap: 5px;">
                        <input type="hidden" name="acao" value="cadastro">

                        <div class="inserirInfo">
                            <label>Nome: </label>
                            <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" required style="padding: 8px 15px;">
                        </div>

                        <div class="inserirInfo">
                            <label>E-mail: </label>
                            <input type="email" name="email" id="email" placeholder="exemplo@email.com" required style="padding: 8px 15px;">
                        </div>

                        <div class="linha_dupla" style="gap: 10px;">
                            <div class="inserirInfo">
                                <label>Senha: </label>
                                <div class="input-group-password">
                                    <input type="password" name="senha" id="senha" placeholder="Mínimo 6 caracteres" required style="padding: 8px 15px;">
                                    <i class="fa-solid fa-eye toggle-password" id="btn-senha"></i>
                                </div>
                            </div>

                            <div class="inserirInfo">
                                <label>Telefone: </label>
                                <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" maxlength="15" oninput="handleTelefone(event)" required style="padding: 8px 15px;">
                            </div>
                        </div>

                        <button class="entrarLogin" type="submit" style="margin-top: 17px; padding: 10px 60px;"> Cadastrar </button>
                    </form>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin: 2px 0;">
                        <div style="flex: 1; height: 1px; background-color: rgba(0, 26, 139, 0.2);"></div>
                        <span style="color: #001a8b; font-size: 11px; font-weight: bold;">OU</span>
                        <div style="flex: 1; height: 1px; background-color: rgba(0, 26, 139, 0.2);"></div>
                    </div>

                    <div id="g_id_onload"
                        data-client_id="904732005700-hhrsns4i5bl2ll22u57bsr9p0ijgkm4e.apps.googleusercontent.com"
                        data-login_uri="http://localhost/Phronesis_/cad.php"
                        data-auto_prompt="false">
                    </div>

                    <div class="google-btn-container" style="display: flex; justify-content: center;">
                        <div class="g_id_signin"
                             data-type="standard"
                             data-size="medium"
                             data-theme="outline"
                             data-text="signup_with"
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
        // Lógica de alternar visibilidade da senha
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

        // Configuração do SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

        <?php if (isset($_SESSION['erro_cad'])): ?>
            Toast.fire({
                icon: 'error',
                title: '<?php echo $_SESSION['erro_cad']; ?>'
            });
        <?php unset($_SESSION['erro_cad']); endif; ?>

        // Validações do Formulário via JavaScript
        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const telefone = document.getElementById('telefone').value;
            
            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!nome || !email || !senha || !telefone) {
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
            } else if (!senhaRegex.test(senha)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    html: `
                        <div style="text-align: left; font-family: Arial, sans-serif;">
                            <b style="display: block; margin-bottom: 8px;">A senha deve ter no mínimo:</b>
                            <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                                <li>6 dígitos;</li>
                                <li>uma letra;</li>
                                <li>um número;</li>
                                <li>um símbolo.</li>
                            </ul>
                        </div>
                    `
                });
            }
        });

        // Máscara do Telefone
        function handleTelefone(event) {
            let input = event.target;
            input.value = telefoneMask(input.value);
        }

        function telefoneMask(value) {
            if (!value) return "";
            value = value.replace(/\D/g, "");
            if (value.length > 0) value = "(" + value;
            if (value.length > 3) value = value.substring(0, 3) + ") " + value.substring(3);
            if (value.length > 10) value = value.substring(0, 10) + "-" + value.substring(10, 14);
            return value;
        }
    </script>

    <?php include 'perfil/vlibras.php'; ?>
</body>
</html>