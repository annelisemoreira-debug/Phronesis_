<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Conta</title>
</head>
<body>

    <h2>Excluir Conta</h2>
    <p>Para excluir sua conta, insira seu e-mail e senha.</p>
    <form action="cad.php" method="POST">
        <input type="hidden" name="acao" value="self_delete">
        
        <label>E-mail:</label>
        <input type="email" name="email" required><br><br>
        
        <label>Senha:</label>
        <input type="password" name="senha" required><br><br>
        
        <button type="submit">Excluir Conta</button>
    </form>

</body>
</html>