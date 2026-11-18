<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica se o parâmetro de ação existe na URL
if(isset($_GET['acao']) && $_GET['acao'] == "recuperar"){ 

// Lê o erro uma única vez e limpa da sessão
$erroRecuperacao = $_SESSION['erro_recuperacao'] ?? '';
unset($_SESSION['erro_recuperacao']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="assets/css/senha.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/img/logos/favicon.webp">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25"></script>

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
    </style>
</head>
<body>
    <img src="assets/img/fundos/fundoRoxo.webp" class="bg-html" alt="Fundo">

    <div class="container">
        <div class="glass-card">
            <div class="logo-section">
                <img src="assets/img/logos/phronesisLogo.webp" alt="Logo oficial da Phronesis">
            </div>

            <div class="mainContainer">
                <h1 class="mainTitulo">Recuperar Senha</h1>
                <p><a class="botaoVoltar" href="login.php" style="text-decoration:none">Pronto para voltar?</a></p>

                <form id="formRecuperar" method="POST" action="cad.php" novalidate
                      data-erro="<?= htmlspecialchars($erroRecuperacao, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="acao" value="nova_senha">

                    <div class="inserirInfo">
                        <label>Email:</label>
                        <input type="email" name="email" id="email" placeholder="exemplo@email.com" maxlength="254" required>
                    </div>

                    <div class="inserirInfo">
                        <label>Nova senha:</label>
                        <input type="password" name="senha" id="senha" placeholder="Digite sua nova senha" minlength="6" maxlength="64" required>
                        <i class="fa-solid fa-eye toggle-password" id="btn-senha"></i>
                    </div>

                    <button class="entrarLogin" type="submit">Atualizar</button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/lib/senhaPerdida.js" defer></script>

    <?php include 'perfil/vlibras.php'; ?>
    <?php include 'interface/footer.php';?>
</body>
</html>

<?php
} else {
    echo "Acesso inválido!"; 
}
?>