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
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <img src="assets/img/fundoRoxo.webp" class="bg-html" alt="Fundo">

    <div class="container">
        <div class="glass-card">
            <div class="logo-section">
                <img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis">
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

    <script src="assets/js/lib/senhaPerdida.js" defer></script>

<?php include '../perfil/vlibras.php'; ?>

</body>
</html>

<?php
} else {
    echo "Acesso inválido!"; 
}
?>