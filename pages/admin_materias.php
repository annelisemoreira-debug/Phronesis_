<?php
session_start();

include '../conexao.php';

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

// 2. LÓGICA PDO
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Erro: " . $e->getMessage()); 
}

$msg = "";

// Lógica de Inserção de Matéria
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

// Lógica de Edição de Matéria
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

// Lógica de Exclusão de Matéria
if (isset($_GET['del'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM materia WHERE id_materia = ?");
        $stmt->execute([$_GET['del']]);
        $msg = "🗑️ Matéria removida!";
    } catch (Exception $e) { 
        $msg = "⚠️ Não é possível remover: matéria está em uso em um cronograma."; 
    }
}

// Lógica de Inserção de Tópico
if (isset($_POST['add_topico'])) {
    $id_materia_topico = $_POST['id_materia'];
    $nome_topico = trim($_POST['nome_topico']);
    if (!empty($nome_topico) && !empty($id_materia_topico)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO materia_topico (id_materia, nome_topico) VALUES (?, ?)");
            $stmt->execute([$id_materia_topico, $nome_topico]);
            $msg = "📌 Tópico adicionado com sucesso!";
        } catch (Exception $e) {
            $msg = "❌ Erro ao adicionar tópico.";
        }
    }
}

// NOVA: Lógica de Edição de Tópico
if (isset($_POST['edit_topico'])) {
    $id_topico = $_POST['id_topico'];
    $novo_nome_topico = trim($_POST['novo_nome_topico']);
    if (!empty($id_topico) && !empty($novo_nome_topico)) {
        try {
            $stmt = $pdo->prepare("UPDATE materia_topico SET nome_topico = ? WHERE id_topico = ?");
            $stmt->execute([$novo_nome_topico, $id_topico]);
            $msg = "📝 Tópico atualizado com sucesso!";
        } catch (Exception $e) {
            $msg = "❌ Erro ao editar tópico.";
        }
    }
}

// Lógica de Exclusão de Tópico
if (isset($_GET['del_topico'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM materia_topico WHERE id_topico = ?");
        $stmt->execute([$_GET['del_topico']]);
        $msg = "🗑️ Tópico removido!";
    } catch (Exception $e) {
        $msg = "❌ Erro ao remover tópico.";
    }
}

// Buscar matérias existentes
$materias = $pdo->query("SELECT * FROM materia ORDER BY nome_materia ASC")->fetchAll(PDO::FETCH_ASSOC);

// Buscar tópicos vinculados organizados por id_materia
$topicos_brutos = $pdo->query("SELECT * FROM materia_topico ORDER BY nome_topico ASC")->fetchAll(PDO::FETCH_ASSOC);
$topicos = [];
foreach ($topicos_brutos as $t) {
    $topicos[$t['id_materia']][] = $t;
}
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
                <h2>Gerenciar Matérias e Tópicos (ADM)</h2>
                <p>Adicione as matérias e liste os subtópicos cobrados para direcionar os estudantes.</p>
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
                            <th style="width: 10%;">ID</th>
                            <th style="width: 55%;">Nome da Matéria</th>
                            <th style="width: 35%; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($materias as $m): $id_m = $m['id_materia']; ?>
                        <tr id="linha-<?= $id_m ?>">
                            <td><b>#<?= $id_m ?></b></td>
                            <td class="col-nome">
                                <span class="texto-nome"><?= htmlspecialchars($m['nome_materia']) ?></span>
                            </td>
                            <td class="col-acoes" style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <button type="button" class="btn-table-action btn-table-topicos" onclick="toggleTopicosPanel(<?= $id_m ?>)" title="Gerenciar Tópicos">
                                        <i class="fa-solid fa-list-check"></i>
                                    </button>

                                    <button type="button" class="btn-table-action btn-table-edit btn-ativar-edicao" 
                                       data-id="<?= $id_m ?>" 
                                       data-nome="<?= htmlspecialchars($m['nome_materia'], ENT_QUOTES, 'UTF-8') ?>" title="Editar Nome">
                                       <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <a href="?del=<?= $id_m ?>" class="btn-table-action btn-table-del" onclick="return confirm('Tem certeza que deseja excluir esta matéria? Todos os tópicos dela sumirão.')" title="Excluir">×</a>
                                </div>
                            </td>
                        </tr>
                        <tr id="painel-topicos-<?= $id_m ?>" class="sub-row-topicos">
                            <td colspan="3">
                                <div class="box-topicos-adm">
                                    <h4>Tópicos de Conteúdo para: <u><?= htmlspecialchars($m['nome_materia']) ?></u></h4>
                                    
                                    <ul class="lista-topicos-atuais">
                                        <?php if(isset($topicos[$id_m])): ?>
                                            <?php foreach($topicos[$id_m] as $t): ?>
                                                <li class="tag-topico-adm" id="tag-topico-<?= $t['id_topico'] ?>">
                                                    <span class="nome-topico-texto"><?= htmlspecialchars($t['nome_topico']) ?></span>
                                                    <i class="fa-solid fa-pen btn-topic-edit" onclick="ativarEdicaoTopico(<?= $t['id_topico'] ?>, '<?= htmlspecialchars($t['nome_topico'], ENT_QUOTES, 'UTF-8') ?>')" title="Editar Tópico"></i>
                                                    <a href="?del_topico=<?= $t['id_topico'] ?>" class="btn-topic-del" onclick="return confirm('Apagar este tópico?')" title="Remover Tópico">×</a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Nenhum subtópico adicionado ainda.</span>
                                        <?php endif; ?>
                                    </ul>

                                    <form method="POST" class="form-add-topico-inline">
                                        <input type="hidden" name="id_materia" value="<?= $id_m ?>">
                                        <input type="text" name="nome_topico" placeholder="Adicionar novo tópico (ex: Estequiometria)" required>
                                        <button type="submit" name="add_topico" style="background-color: var(--purple-main); color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:600;">
                                            Adicionar
                                        </button>
                                    </form>
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

<script src="../assets/js/lib/admin_materias.js" defer></script>

<?php include '../perfil/vlibras.php'; ?>

</body>
</html>