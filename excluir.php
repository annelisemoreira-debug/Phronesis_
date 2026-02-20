<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h2>Alterar Funcionário</h2>
        <hr>
        <form action="cad.php" method="POST">
            
            <input type="hidden" name="idusuario" value="<?php echo $row['idusuario']; ?>">

            <label>E-mail:</label>
            <input type="text" name="email" value="<?php echo $row['email']; ?>"><br><br>

            <label>Senha:</label>
            <input type="text" name="senha" value="<?php echo $row['senha']; ?>"><br><br>

            <button type="submit">Confirmar Alteração</button>  

            
        </form>

    
    <form action="cad.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este funcionário?')">

            <input type="hidden" name="acao" value="delete">
            <input type="hidden" name="idusuario" value="<?php echo $row['idusuario']; ?>">
            <button type="submit" class="excluir">Excluir Funcionário</button>
        </form>

</body>
</html>