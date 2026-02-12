<?php
// Verifica se o parâmetro de ação existe na URL
if(isset($_GET['acao']) && $_GET['acao'] == "recuperar"){
?>
    <h2>Recuperar Senha</h2>
    <form method="POST" action="cad.php">
        <input type="hidden" name="acao" value="nova_senha">

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Nova senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Atualizar</button>
    </form>
<?php
} else {
    echo "Acesso inválido!";
}
?>