<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Faça parte do Phronesis!</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
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
                                <input type="password" name="senha" id="senha"
                                    placeholder="Mínimo 6 caracteres (letra, número e símbolo)"
                                    required>
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
        // Configuração padrão do Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

        // 1. Captura mensagens vindas do PHP (cad.php)
        <?php if (isset($_SESSION['erro_cad'])): ?>
            Toast.fire({
                icon: 'error',
                title: '<?php echo $_SESSION['erro_cad']; ?>'
            });
        <?php unset($_SESSION['erro_cad']); endif; ?>

        // 2. Validação Manual para substituir os avisos padrão do navegador
        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const telefone = document.getElementById('telefone').value;
            
            // Regex da senha (mesma do seu cad.php)
            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let erro = "";

                // Verificação de campos vazios
            if (!nome || !email || !senha || !telefone) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Por favor, preencha todos os campos."
                });
            } 
            // Verificação de e-mail
            else if (!emailRegex.test(email)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Formato de e-mail inválido."
                });
            } 
            // Verificação de senha com formato de lista
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

        // Sua função de máscara (mantida original)
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