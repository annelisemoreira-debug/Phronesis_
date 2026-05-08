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
                <p style="color: #031768">Pronto para voltar? Redefina a senha!</p>

                <?php if(isset($_SESSION['erro_recuperacao'])): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'error',
                                title: '<?php echo $_SESSION['erro_recuperacao']; ?>'
                            });
                        });
                    </script>
                    <?php unset($_SESSION['erro_recuperacao']); ?>
                <?php endif; ?>

                <form method="POST" action="cad.php">
                    <input type="hidden" name="acao" value="nova_senha">

                    <div class="inserirInfo">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="inserirInfo">
                        <label>Nova senha:</label>
                        <input type="password" name="senha" required>
                    </div>

                    <button class="entrarLogin" type="submit">Atualizar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
} else {
    echo "Acesso inválido!"; 
}
?>