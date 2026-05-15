<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Faça parte do Phronesis!</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Estrutura para centralização precisa */
        .inserirInfo { 
            display: flex;
            flex-direction: column;
        }

        /* Contêiner relativo apenas para o campo de input */
        .input-group-password {
            position: relative;
            display: flex;
            align-items: center; /* Centraliza verticalmente o ícone em relação ao input */
        }

        .input-group-password input {
            width: 100%;
            padding-right: 45px; /* Espaço para o ícone não cobrir o texto */
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #031768;
            z-index: 10;
            font-size: 1.1rem;
            /* Removemos o top: 65% e usamos alinhamento flex centralizado */
        }

        /* Ajuste visual para o formulário */
        .linha_dupla {
            display: flex;
            gap: 15px;
        }
        
        .linha_dupla .inserirInfo {
            flex: 1;
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
                    <h1 class="mainTitulo">Faça seu cadastro!</h1>
                    <p style="color: #031768">Já possui uma conta? <a class="botaoLogin" href="login.php"> Entrar </a></p>

                    <form id="formCadastro" method="POST" action="cad.php" novalidate>
                        <input type="hidden" name="acao" value="cadastro">

                        <div class="inserirInfo">
                            <label>Nome: </label>
                            <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" required>
                        </div>

                        <div class="inserirInfo">
                            <label>E-mail: </label>
                            <input type="email" name="email" id="email" placeholder="exemplo@email.com" required>
                        </div>

                        <div class="linha_dupla">
                            <div class="inserirInfo">
                                <label>Senha: </label>
                                <div class="input-group-password">
                                    <input type="password" name="senha" id="senha"
                                        placeholder="Mínimo 6 caracteres"
                                        required>
                                    <i class="fa-solid fa-eye toggle-password" id="btn-senha"></i>
                                </div>
                            </div>

                            <div class="inserirInfo">
                                <label>Telefone: </label>
                                <input type="text" name="telefone" id="telefone"
                                    placeholder="(00) 00000-0000" maxlength="15"
                                    oninput="handleTelefone(event)" required>
                            </div>
                        </div>

                        <button class="entrarLogin" type="submit"> Cadastrar </button>
                    </form>
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

        <?php if (isset($_SESSION['erro_cad'])): ?>
            Toast.fire({
                icon: 'error',
                title: '<?php echo $_SESSION['erro_cad']; ?>'
            });
        <?php unset($_SESSION['erro_cad']); endif; ?>

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
            } 
            else if (!emailRegex.test(email)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Formato de e-mail inválido."
                });
            } 
            else if (!senhaRegex.test(senha)) {
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
</body>
</html>