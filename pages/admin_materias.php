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
$stmt_check = $conn->prepare("SELECT tipo, foto FROM usuario WHERE id_usuario = ?");
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
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Matérias - ADM</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_materias.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">

</head>
<body>

<?php include '../interface/header.php'; ?>


<main class="main-container">
    <section class="card-principal">
        
        <div class="card-header">
            <span class="icon-purple-box">
                <i class="fa-solid fa-book"></i>
            </span>
            <div>
                <h2>Gerenciar Matérias (ADM)</h2>
                <p>Adicione e gerencie as matérias disponíveis para os cronogramas.</p>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="alert-message"><?= $msg ?></div>
        <?php endif; ?>
        
        <form method="POST" class="input-group-append">
            <input type="text" name="nome_materia" class="input-materia" placeholder="Nome da Matéria (ex: Química)" required>
            <button type="submit" name="add_materia" class="btn-adicionar">
                Adicionar <i class="fa-solid fa-plus"></i>
            </button>
        </form>

        <?php if(empty($materias)): ?>
            <div class="empty-table-state">
                <div class="book-purple-icon">
                    <i class="fa-solid fa-book-open"></i>
                    <span class="sparkle-1">✦</span>
                    <span class="sparkle-2">✦</span>
                </div>
                <h4>Nenhuma matéria cadastrada.</h4>
                <p>Adicione sua primeira matéria para começar.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">ID</th>
                            <th style="width: 65%;">Nome da Matéria</th>
                            <th style="width: 20%; text-align: center;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($materias as $m): ?>
                        <tr id="linha-<?= $m['id_materia'] ?>">
                            <td><b>#<?= $m['id_materia'] ?></b></td>
                            
                            <td class="col-nome">
                                <span class="texto-nome"><?= htmlspecialchars($m['nome_materia']) ?></span>
                            </td>
                            
                            <td class="col-acoes" style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <button type="button" class="btn-table-action btn-table-edit btn-ativar-edicao" 
                                       data-id="<?= $m['id_materia'] ?>" 
                                       data-nome="<?= htmlspecialchars($m['nome_materia'], ENT_QUOTES, 'UTF-8') ?>" title="Editar">
                                       <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <a href="?del=<?= $m['id_materia'] ?>" class="btn-table-action btn-table-del" onclick="return confirm('Tem certeza que deseja excluir esta matéria?')" title="Excluir">×</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a href="../index.php" class="btn-back-home">
            <i class="fa-solid fa-arrow-left"></i> Voltar para a Home
        </a>
    </section>
</main>

<script>
// Monitoramento dinâmico de edição inline
document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-ativar-edicao');
    if (btn) {
        const id = btn.getAttribute('data-id');
        const nomeOriginal = btn.getAttribute('data-nome');
        ativarEdicao(id, nomeOriginal); // Corrigido para corresponder ao nome da função abaixo
    }
});

function ativarEdicao(id, nomeOriginal) { // Corrigido aqui (removido o 'c')
    const linha = document.getElementById(`linha-${id}`);
    const colNome = linha.querySelector('.col-nome');
    const colAcoes = linha.querySelector('.col-acoes');

    const htmlOriginalNome = colNome.innerHTML;
    const htmlOriginalAcoes = colAcoes.innerHTML;

    // Troca o texto estático pelo campo de entrada ajustado
    colNome.innerHTML = `<input type="text" class="input-inline" id="input-${id}">`;
    document.getElementById(`input-${id}`).value = nomeOriginal;
    document.getElementById(`input-${id}`).focus();

    // Substitui temporariamente as ações por Salvar/Cancelar
    colAcoes.innerHTML = `
        <div class="action-buttons" style="justify-content: center;">
            <form method="POST" style="display: inline;" onsubmit="prepararEnvio(${id});">
                <input type="hidden" name="id_materia" value="${id}">
                <input type="hidden" name="novo_nome" id="hidden-nome-${id}">
                <button type="submit" name="edit_materia" class="btn-salvar">Salvar</button>
            </form>
            <button type="button" class="btn-cancelar" id="cancelar-${id}">X</button>
        </div>
    `;

    // Evento para Reverter alteração caso clique em cancelar
    document.getElementById(`cancelar-${id}`).addEventListener('click', () => {
        colNome.innerHTML = htmlOriginalNome;
        colAcoes.innerHTML = htmlOriginalAcoes;
    });
}

function prepararEnvio(id) {
    const valorInput = document.getElementById(`input-${id}`).value;
    document.getElementById(`hidden-nome-${id}`).value = valorInput;
}

(function () {
    const savedTheme = localStorage.getItem('site-theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme'); // Adiciona no <html>
        document.body.classList.add('dark-theme');            // Mantém no <body> para o restante do seu admin_materias.css
    }
})();
    
</script>

<?php include '../perfil/vlibras.php'; ?>

</body>
</html>