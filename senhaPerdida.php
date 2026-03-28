<?php
// Verifica se o parâmetro de ação existe na URL
if(isset($_GET['acao']) && $_GET['acao'] == "recuperar"){ // Se a acao do GET for igual a recuperar
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="assets/css/senha.css">
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
                <form method="POST" action="cad.php">
                    <input type="hidden" name="acao" value="nova_senha"> <!--O valor da ação é nova_senha-->

                    <div class="inserirInfo">
                        <label>Email:</label>
                        <input type="email" name="email" required><br>
                    </div>

                    <div class="inserirInfo">
                        <label>Nova senha:</label>
                        <input type="password" name="senha" required><br>
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
    echo "Acesso inválido!"; // Se o email estiver inválido, vai aparecer Acesso inválido
}
?>