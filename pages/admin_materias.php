<?php
session_start();

include '../conexao.php'; // Usa o $conn nativo do seu sistema

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

$msg = "";

// Lógica de Inserção de Matéria
if (isset($_POST['add_materia'])) {
    $nome = trim($_POST['nome_materia']);
    if (!empty($nome)) {
        $stmt = $conn->prepare("INSERT INTO materia (nome_materia) VALUES (?)");
        $stmt->bind_param("s", $nome);
        if ($stmt->execute()) {
            $msg = "✅ Matéria '$nome' adicionada!";
        } else { 
            $msg = "❌ Erro: Matéria já existe ou falha no banco."; 
        }
        $stmt->close();
    }
}

// Lógica de Edição de Matéria
if (isset($_POST['edit_materia'])) {
    $id_edit = $_POST['id_materia'];
    $nome_edit = trim($_POST['novo_nome']);
    if (!empty($nome_edit) && !empty($id_edit)) {
        $stmt = $conn->prepare("UPDATE materia SET nome_materia = ? WHERE id_materia = ?");
        $stmt->bind_param("si", $nome_edit, $id_edit);
        if ($stmt->execute()) {
            $msg = "📝 Matéria atualizada com sucesso!";
        } else {
            $msg = "❌ Erro ao editar matéria.";
        }
        $stmt->close();
    }
}

// Lógica de Exclusão de Matéria
if (isset($_GET['del'])) {
    $id_del = $_GET['del'];
    try {
        $stmt = $conn->prepare("DELETE FROM materia WHERE id_materia = ?");
        $stmt->bind_param("i", $id_del);
        if ($stmt->execute()) {
            $msg = "🗑️ Matéria removida!";
        } else {
            $msg = "⚠️ Não é possível remover: matéria está em uso em um cronograma.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $msg = "⚠️ Não é possível remover: matéria está em uso em um cronograma."; 
    }
}

// Lógica de Inserção de Tópico
if (isset($_POST['add_topico'])) {
    $id_materia_topico = $_POST['id_materia'];
    $nome_topico = trim($_POST['nome_topico']);
    if (!empty($nome_topico) && !empty($id_materia_topico)) {
        $stmt = $conn->prepare("INSERT INTO materia_topico (id_materia, nome_topico) VALUES (?, ?)");
        $stmt->bind_param("is", $id_materia_topico, $nome_topico);
        if ($stmt->execute()) {
            $msg = "📌 Tópico adicionado com sucesso!";
        } else {
            $msg = "❌ Erro ao adicionar tópico.";
        }
        $stmt->close();
    }
}

// Lógica de Edição de Tópico
if (isset($_POST['edit_topico'])) {
    $id_topico = $_POST['id_topico'];
    $novo_nome_topico = trim($_POST['novo_nome_topico']);
    if (!empty($id_topico) && !empty($novo_nome_topico)) {
        $stmt = $conn->prepare("UPDATE materia_topico SET nome_topico = ? WHERE id_topico = ?");
        $stmt->bind_param("si", $novo_nome_topico, $id_topico);
        if ($stmt->execute()) {
            $msg = "📝 Tópico atualizado com sucesso!";
        } else {
            $msg = "❌ Erro ao editar tópico.";
        }
        $stmt->close();
    }
}

// Lógica de Exclusão de Tópico
if (isset($_GET['del_topico'])) {
    $id_del_topico = $_GET['del_topico'];
    $stmt = $conn->prepare("DELETE FROM materia_topico WHERE id_topico = ?");
    $stmt->bind_param("i", $id_del_topico);
    if ($stmt->execute()) {
        $msg = "🗑️ Tópico removido!";
    } else {
        $msg = "❌ Erro ao remover tópico.";
    }
    $stmt->close();
}
 
// ===================== LIVROS =====================
$VESTIBULARES = ['fuvest' => 'Fuvest', 'unicamp' => 'Unicamp', 'enem' => 'Enem'];
 
// Valida e salva a capa. Retorna: caminho (sucesso) | null (nenhum arquivo) | false (erro)
function salvarCapa($file, &$erro) {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3 * 1024 * 1024) {
        $erro = 'Capa inválida ou maior que 3 MB.';
        return false;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) {
        $erro = 'A capa precisa ser JPG, PNG ou WEBP.';
        return false;
    }
    $dir = __DIR__ . '/../assets/img/livros/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $nome = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $nome)) {
        $erro = 'Não foi possível salvar a imagem da capa.';
        return false;
    }
    return 'assets/img/livros/' . $nome;
}
 
function apagarArquivoCapa($caminho) {
    if ($caminho && strpos($caminho, 'assets/img/livros/') === 0) {
        $f = __DIR__ . '/../' . $caminho;
        if (is_file($f)) @unlink($f);
    }
}
 
function salvarVestibulares($conn, $idLivro, $lista, $validos) {
    $stmt = $conn->prepare("DELETE FROM livro_vestibular WHERE id_livro = ?");
    $stmt->bind_param("i", $idLivro);
    $stmt->execute();
    $stmt->close();
 
    $stmt = $conn->prepare("INSERT INTO livro_vestibular (id_livro, vestibular) VALUES (?, ?)");
    foreach ((array) $lista as $v) {
        if (isset($validos[$v])) {
            $stmt->bind_param("is", $idLivro, $v);
            $stmt->execute();
        }
    }
    $stmt->close();
}
 
// Adicionar livro
if (isset($_POST['add_livro'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $autor  = trim($_POST['autor'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');
 
    if ($titulo === '' || $autor === '' || $resumo === '') {
        $msg = "❌ Preencha título, autor e resumo.";
    } else {
        $erroCapa = '';
        $capa = salvarCapa($_FILES['capa'] ?? [], $erroCapa);
        if ($capa === false) {
            $msg = "❌ $erroCapa";
        } else {
            $stmt = $conn->prepare("INSERT INTO livro (titulo, autor, resumo, capa) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $titulo, $autor, $resumo, $capa);
            if ($stmt->execute()) {
                salvarVestibulares($conn, $conn->insert_id, $_POST['vestibulares'] ?? [], $VESTIBULARES);
                $msg = "📚 Livro '$titulo' adicionado!";
            } else {
                $msg = "❌ Erro ao adicionar livro.";
            }
            $stmt->close();
        }
    }
}
 
// Editar livro
if (isset($_POST['edit_livro'])) {
    $idLivro = (int) ($_POST['id_livro'] ?? 0);
    $titulo  = trim($_POST['titulo'] ?? '');
    $autor   = trim($_POST['autor'] ?? '');
    $resumo  = trim($_POST['resumo'] ?? '');
 
    if ($idLivro <= 0 || $titulo === '' || $autor === '' || $resumo === '') {
        $msg = "❌ Preencha título, autor e resumo.";
    } else {
        $erroCapa = '';
        $capaNova = salvarCapa($_FILES['capa'] ?? [], $erroCapa);
        if ($capaNova === false) {
            $msg = "❌ $erroCapa";
        } else {
            if ($capaNova !== null) {
                $st = $conn->prepare("SELECT capa FROM livro WHERE id_livro = ?");
                $st->bind_param("i", $idLivro);
                $st->execute();
                $antiga = $st->get_result()->fetch_assoc()['capa'] ?? null;
                $st->close();
                apagarArquivoCapa($antiga);
            }
            $stmt = $conn->prepare("UPDATE livro SET titulo = ?, autor = ?, resumo = ?, capa = COALESCE(?, capa) WHERE id_livro = ?");
            $stmt->bind_param("ssssi", $titulo, $autor, $resumo, $capaNova, $idLivro);
            if ($stmt->execute()) {
                salvarVestibulares($conn, $idLivro, $_POST['vestibulares'] ?? [], $VESTIBULARES);
                $msg = "📝 Livro atualizado com sucesso!";
            } else {
                $msg = "❌ Erro ao editar livro.";
            }
            $stmt->close();
        }
    }
}
 
// Excluir livro
if (isset($_GET['del_livro'])) {
    $idLivro = (int) $_GET['del_livro'];
    $st = $conn->prepare("SELECT capa FROM livro WHERE id_livro = ?");
    $st->bind_param("i", $idLivro);
    $st->execute();
    $antiga = $st->get_result()->fetch_assoc()['capa'] ?? null;
    $st->close();
 
    $stmt = $conn->prepare("DELETE FROM livro WHERE id_livro = ?");
    $stmt->bind_param("i", $idLivro);
    if ($stmt->execute()) {
        apagarArquivoCapa($antiga);
        $msg = "🗑️ Livro removido!";
    } else {
        $msg = "❌ Erro ao remover livro.";
    }
    $stmt->close();
}
 
// Buscar livros cadastrados
$livros = [];
$res_livros = $conn->query(
    "SELECT l.*, GROUP_CONCAT(lv.vestibular) AS vests
       FROM livro l
       LEFT JOIN livro_vestibular lv ON lv.id_livro = l.id_livro
      GROUP BY l.id_livro
      ORDER BY l.titulo ASC"
);
if ($res_livros) {
    while ($row = $res_livros->fetch_assoc()) { $livros[] = $row; }
}
 

// Buscar matérias existentes
$materias = [];
$res_materias = $conn->query("SELECT * FROM materia ORDER BY nome_materia ASC");
if ($res_materias) {
    while ($row = $res_materias->fetch_assoc()) {
        $materias[] = $row;
    }
}

// Buscar tópicos vinculados organizados por id_materia
$topicos = [];
$res_topicos = $conn->query("SELECT * FROM materia_topico ORDER BY nome_topico ASC");
if ($res_topicos) {
    while ($t = $res_topicos->fetch_assoc()) {
        $topicos[$t['id_materia']][] = $t;
    }
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
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.png">

    <script>
        (function () {
            const savedTheme = localStorage.getItem('site-theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
</head>
<body>

<?php include '../interface/header.php'; ?>

<main class="main-container">

    <?php if ($msg): ?>
        <div class="alert-message"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- ================= CARD 1: LEITURAS OBRIGATÓRIAS ================= -->
    <section class="card-principal card-livros" id="livros-adm">
        <div class="card-header">
            <span class="icon-purple-box"><i class="fa-solid fa-book-bookmark"></i></span>
            <div>
                <h2>Gerenciar Leituras Obrigatórias</h2>
                <p>Cadastre os livros, o resumo e em quais vestibulares eles são cobrados.</p>
            </div>
        </div>
    
        <form method="POST" enctype="multipart/form-data" class="form-livro">
            <div class="grid-livro">
                <input type="text" name="titulo" class="campo-livro" placeholder="Título da obra" maxlength="255" required>
                <input type="text" name="autor" class="campo-livro" placeholder="Autor(a)" maxlength="255" required>
            </div>
            <textarea name="resumo" class="campo-livro" rows="4" placeholder="Breve resumo da obra" required></textarea>
    
            <div class="vest-checks">
                <?php foreach ($VESTIBULARES as $k => $nomeV): ?>
                    <label class="chip-check">
                        <input type="checkbox" name="vestibulares[]" value="<?= $k ?>">
                        <span><?= $nomeV ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
    
            <div class="linha-envio">
                <label class="upload-capa">
                    <i class="fa-solid fa-image"></i> Capa (JPG, PNG ou WEBP, até 3 MB)
                    <input type="file" name="capa" accept="image/jpeg,image/png,image/webp">
                </label>
                <button type="submit" name="add_livro" class="btn-adicionar">
                    Adicionar livro <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </form>
    
        <?php if (empty($livros)): ?>
            <div class="empty-table-state">
                <div class="book-purple-icon"><i class="fa-solid fa-book-open"></i></div>
                <h4>Nenhum livro cadastrado.</h4>
                <p>Adicione a primeira obra para ela aparecer na página de leituras.</p>
            </div>
        <?php else: ?>
                <div class="table-wrapper tabela-livros <?= count($livros) > 5 ? 'rolagem' : '' ?>">
                    <table>
                        <thead>
                            <tr>
                            <th style="width: 10%;">Capa</th>
                            <th style="width: 40%;">Obra</th>
                            <th style="width: 30%;">Vestibulares</th>
                            <th style="width: 20%; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livros as $l): $vl = $l['vests'] ? explode(',', $l['vests']) : []; ?>
                        <tr>
                            <td>
                                <?php if ($l['capa']): ?>
                                    <img class="thumb-livro" src="../<?= htmlspecialchars($l['capa']) ?>" alt="">
                                <?php else: ?>
                                    <span class="thumb-livro thumb-vazio"><i class="fa-solid fa-book"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <b><?= htmlspecialchars($l['titulo']) ?></b><br>
                                <small><?= htmlspecialchars($l['autor']) ?></small>
                            </td>
                            <td>
                                <?php foreach ($vl as $v): ?>
                                    <span class="tag-adm tag-adm-<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($VESTIBULARES[$v] ?? $v) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <button type="button" class="btn-table-action btn-table-edit btn-editar-livro" title="Editar livro"
                                        data-id="<?= (int)$l['id_livro'] ?>"
                                        data-titulo="<?= htmlspecialchars($l['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-autor="<?= htmlspecialchars($l['autor'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-resumo="<?= htmlspecialchars($l['resumo'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-vests="<?= htmlspecialchars(implode(',', $vl), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="?del_livro=<?= (int)$l['id_livro'] ?>" class="btn-table-action btn-table-del"
                                    onclick="return confirm('Excluir este livro? Favoritos e progresso dos usuários nele também serão apagados.')" title="Excluir">×</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    
        <!-- Janela de edição -->
        <dialog id="dlg-livro" class="dlg-livro">
            <form method="POST" enctype="multipart/form-data" class="form-livro">
                <h3>Editar livro</h3>
                <input type="hidden" name="id_livro">
                <div class="grid-livro">
                    <input type="text" name="titulo" class="campo-livro" maxlength="255" required>
                    <input type="text" name="autor" class="campo-livro" maxlength="255" required>
                </div>
                <textarea name="resumo" class="campo-livro" rows="6" required></textarea>
                <div class="vest-checks">
                    <?php foreach ($VESTIBULARES as $k => $nomeV): ?>
                        <label class="chip-check">
                            <input type="checkbox" name="vestibulares[]" value="<?= $k ?>">
                            <span><?= $nomeV ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <label class="upload-capa">
                    <i class="fa-solid fa-image"></i> Trocar capa (opcional)
                    <input type="file" name="capa" accept="image/jpeg,image/png,image/webp">
                </label>
                <div class="linha-envio">
                    <button type="button" class="btn-cancelar fechar-dlg">Cancelar</button>
                    <button type="submit" name="edit_livro" class="btn-salvar">Salvar alterações</button>
                </div>
            </form>
        </dialog>
    </section>

    <!-- ================= CARD 2: MATÉRIAS E TÓPICOS ================= -->
    <section class="card-principal card-materias" id="materias-adm">
        <div class="card-header">
            <span class="icon-purple-box">
                <i class="fa-solid fa-book"></i>
            </span>
            <div>
                <h2>Gerenciar Matérias e Tópicos (ADM)</h2>
                <p>Adicione as matérias e liste os subtópicos cobrados para direcionar os estudantes.</p>
            </div>
        </div>
        
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

    </section>

    <a href="../index.php" class="btn-back-home">
        <i class="fa-solid fa-arrow-left"></i> Voltar para a Home
    </a>
</main>

<script src="../assets/js/lib/admin_materias.js" defer></script>

<?php include '../perfil/vlibras.php'; ?>
<?php include '../interface/footer.php';?>

</body>
</html>