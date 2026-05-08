<?php
session_start();
// Verifica se o parâmetro de ação existe na URL
if(isset($_GET['acao']) && $_GET['acao'] == "recuperar"){ 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="assets/css/senha.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <img src="assets/img/fundoRoxo.png" class="bg-html" alt="Fundo">

    <div class="container">
        <div class="glass-card">
            <div class="logo-section">
                <img src="assets/img/phronesisLogo.png" alt="Logo oficial da Phronesis">
            </div>

            <div class="mainContainer">
                <h1 class="mainTitulo">Recuperar Senha</h1>
                <p><a class="botaoVoltar" href="login.php" style="text-decoration:none">Pronto para voltar?</a></p>

                <form id="formRecuperar" method="POST" action="cad.php" novalidate>
                    <input type="hidden" name="acao" value="nova_senha">

                    <div class="inserirInfo">
                        <label>Email:</label>
                        <input type="email" name="email" id="email" placeholder="exemplo@email.com" required>
                    </div>

                    <div class="inserirInfo">
                        <label>Nova senha:</label>
                        <input type="password" name="senha" id="senha" placeholder="Digite sua nova senha" required>
                    </div>

                    <button class="entrarLogin" type="submit">Atualizar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Configuração padrão do Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

        // 1. Captura mensagens vindas da SESSION PHP (Erros do servidor)
        <?php if(isset($_SESSION['erro_recuperacao'])): ?>
            Toast.fire({
                icon: 'error',
                title: '<?php echo $_SESSION['erro_recuperacao']; ?>'
            });
        <?php unset($_SESSION['erro_recuperacao']); endif; ?>

        // 2. Validação Manual para substituir os avisos padrão do navegador
        document.getElementById('formRecuperar').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Regra de senha forte (Mínimo 6 caracteres, letra, número e símbolo)
            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/;

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
            } else if (!senhaRegex.test(senha)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    html: `
                        <div style="text-align: left; font-family: Arial, sans-serif;">
                            <b style="display: block; margin-bottom: 8px;">A nova senha deve ter no mínimo:</b>
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
    </script>
</body>
</html>

<?php
} else {
    echo "Acesso inválido!"; 
}
?>