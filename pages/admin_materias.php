<?php
// Conexão com o banco (ajuste host, dbname, user e pass)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=phronesis", "root", "Home@spSENAI2025!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erro: " . $e->getMessage()); }

$msg = "";

// Lógica de Inserção
if (isset($_POST['add_materia'])) {
    $nome = trim($_POST['nome_materia']);
    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO materia (nome_materia) VALUES (?)");
            $stmt->execute([$nome]);
            $msg = "✅ Matéria '$nome' adicionada!";
        } catch (Exception $e) { $msg = "❌ Erro: Matéria já existe ou falha no banco."; }
    }
}

// Lógica de Exclusão
if (isset($_GET['del'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM materia WHERE id_materia = ?");
        $stmt->execute([$_GET['del']]);
        $msg = "🗑️ Matéria removida!";
    } catch (Exception $e) { $msg = "⚠️ Não é possível remover: matéria está em uso em um cronograma."; }
}

// Buscar matérias existentes
$materias = $pdo->query("SELECT * FROM materia ORDER BY nome_materia ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>ADM - Gerenciar Matérias</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 50px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; }
        input { width: 70%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px; background: #34a853; color: white; border: none; cursor: pointer; border-radius: 4px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        td, th { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .del { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Gerenciar Matérias (ADM)</h2>
        <?php if($msg) echo "<p>$msg</p>"; ?>
        
        <form method="POST">
            <input type="text" name="nome_materia" placeholder="Nome da Matéria (ex: Química)" required>
            <button type="submit" name="add_materia">Add</button>
        </form>

        <table>
            <tr><th>ID</th><th>Nome</th><th>Ação</th></tr>
            <?php foreach($materias as $m): ?>
            <tr>
                <td><?= $m['id_materia'] ?></td>
                <td><?= htmlspecialchars($m['nome_materia']) ?></td>
                <td><a href="?del=<?= $m['id_materia'] ?>" class="del">×</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <a href="cronograma.php">⬅️ Ir para o Cronograma</a>
    </div>
</body>
</html>