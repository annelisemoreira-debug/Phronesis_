<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Conta</title>
    <link rel="stylesheet" href="assets/css/excluir.css">
</head>

<body>
    
    <main>
        <div class="mainContainer">
            <h2 class=" mainTitulo, yeseva-one-regular">Excluir Conta</h2>
            <p>Para excluir sua conta, insira seu e-mail e senha.</p>
            <form action="cad.php" method="POST">
                <input type="hidden" name="acao" value="self_delete"> <!-- A ação é definida como self_delete -->
                <div class="inserirInfo">
                <label>E-mail:</label>
                <input type="email" name="email" required><br><br>
                </div>

                <div class="inserirInfo">
                <label>Senha:</label>
                <input type="password" name="senha" required><br><br>
                </div>
                <button class="entrarLogin" type="submit">Excluir Conta</button>
            </form>
        </div>
    </main>

</body>
</html>