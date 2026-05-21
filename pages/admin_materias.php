
<?php
session_start();

// 1. CONEXÃO COM O BANCO (Usando MySQLi para checar o admin antes do PDO)
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);

// SEGURANÇA: Se não tiver ID na sessão, nem tenta o resto
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// BUSCA O TIPO DIRETO NO BANCO PARA GARANTIR
$id_usuario = $_SESSION['id'];
$stmt_check = $conn->prepare("SELECT tipo FROM usuario WHERE id_usuario = ?");
$stmt_check->bind_param("i", $id_usuario);
$stmt_check->execute();
$user_data = $stmt_check->get_result()->fetch_assoc();

// Se não for admin, volta para a index
if (!$user_data || $user_data['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 2. LÓGICA PDO (Para o restante do seu código funcionar)
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Erro: " . $e->getMessage()); 
}

$msg = "";

// Lógica de Inserção
if (isset($_POST['add_materia'])) {
    $nome = trim($_POST['nome_materia']);
    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO materia (nome_materia) VALUES (?)");
            $stmt->execute([$nome]);
            $msg = "✅ Matéria '$nome' adicionada!";
        } catch (Exception $e) { 
            $msg = "❌ Erro: Matéria já existe ou falha no banco."; 
        }
    }
}

// Lógica de Edição
if (isset($_POST['edit_materia'])) {
    $id_edit = $_POST['id_materia'];
    $nome_edit = trim($_POST['novo_nome']);
    if (!empty($nome_edit) && !empty($id_edit)) {
        try {
            $stmt = $pdo->prepare("UPDATE materia SET nome_materia = ? WHERE id_materia = ?");
            $stmt->execute([$nome_edit, $id_edit]);
            $msg = "📝 Matéria atualizada com sucesso!";
        } catch (Exception $e) {
            $msg = "❌ Erro ao editar matéria.";
        }
    }
}

// Lógica de Exclusão
if (isset($_GET['del'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM materia WHERE id_materia = ?");
        $stmt->execute([$_GET['del']]);
        $msg = "🗑️ Matéria removida!";
    } catch (Exception $e) { 
        $msg = "⚠️ Não é possível remover: matéria está em uso em um cronograma."; 
    }
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
        .del { color: red; text-decoration: none; font-weight: bold; margin-left: 10px; }
        .edit { color: #1a73e8; text-decoration: none; font-weight: bold; cursor: pointer; }
        .voltar { display: inline-block; margin-top: 20px; text-decoration: none; color: #666; font-size: 14px; }
        
        .input-inline { padding: 5px; width: 80%; border: 1px solid #1a73e8; border-radius: 4px; }
        .btn-salvar { background: #1a73e8; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-right: 5px; font-size: 12px; }
        .btn-cancelar { background: #666; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; }
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
            <thead>
                <tr><th>ID</th><th>Nome</th><th>Ação</th></tr>
            </thead>
            <tbody>
                <?php foreach($materias as $m): ?>
                <tr id="linha-<?= $m['id_materia'] ?>">
                    <td><?= $m['id_materia'] ?></td>
                    
                    <td class="col-nome">
                        <span class="texto-nome"><?= htmlspecialchars($m['nome_materia']) ?></span>
                    </td>
                    
                    <td class="col-acoes">
                        <a class="edit btn-ativar-edicao" 
                           data-id="<?= $m['id_materia'] ?>" 
                           data-nome="<?= htmlspecialchars($m['nome_materia'], ENT_QUOTES, 'UTF-8') ?>">✏️</a>
                        
                        <a href="?del=<?= $m['id_materia'] ?>" class="del" onclick="return confirm('Excluir?')">×</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <a href="../index.php" class="voltar">⬅️ Voltar para a Home</a>
    </div>

    <script>
    // Monitora os cliques nos botões de editar de forma limpa e segura
    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-ativar-edicao')) {
            const id = e.target.getAttribute('data-id');
            const nomeAtual = e.target.getAttribute('data-nome');
            ativarEdicao(id, nomeAtual);
        }
    });

    function ativarEdicao(id, nomeAtual) {
        const linha = document.getElementById(`linha-${id}`);
        const colNome = linha.querySelector('.col-nome');
        const colAcoes = linha.querySelector('.col-acoes');

        // Salva o estado original caso queira cancelar
        const htmlOriginalNome = colNome.innerHTML;
        const htmlOriginalAcoes = colAcoes.innerHTML;

        // Troca para o input inline
        colNome.innerHTML = `<input type="text" class="input-inline" id="input-${id}">`;
        // Define o valor via propriedade para evitar bugs com caracteres do tipo " e '
        document.getElementById(`input-${id}`).value = nomeAtual;

        // Altera os botões de ação
        colAcoes.innerHTML = `
            <form method="POST" style="display: inline;" onsubmit="prepararEnvio(${id});">
                <input type="hidden" name="id_materia" value="${id}">
                <input type="hidden" name="novo_nome" id="hidden-nome-${id}">
                <button type="submit" name="edit_materia" class="btn-salvar">Salvar</button>
            </form>
            <button type="button" class="btn-cancelar" id="cancelar-${id}">X</button>
        `;

        // Evento de cancelar restaura os botões anteriores
        document.getElementById(`cancelar-${id}`).addEventListener('click', () => {
            colNome.innerHTML = htmlOriginalNome;
            colAcoes.innerHTML = htmlOriginalAcoes;
        });
    }

    function prepararEnvio(id) {
        const valorInput = document.getElementById(`input-${id}`).value;
        document.getElementById(`hidden-nome-${id}`).value = valorInput;
    }
    </script>
</body>
</html>

