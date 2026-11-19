<?php
session_start();

include '../conexao.php'; // Usa o $conn nativo do seu sistema

// SEGURANÇA: Se não tiver ID na sessão, nem tenta o resto
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// BUSCA O TIPO DIRETO NO BANCO PARA GARANTIR
$id_usuario = (int) $_SESSION['id'];
$stmt_check = $conn->prepare("SELECT tipo, foto FROM usuario WHERE id_usuario = ?");
$stmt_check->bind_param("i", $id_usuario);
$stmt_check->execute();
$user_data = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

// Se não for admin, volta para a index
if (!$user_data || $user_data['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$msg = "";

// =========================================================================
// LIMITES DE CARACTERES
// Fonte única: usados na validação do servidor E no maxlength do HTML.
// (O admin_materias.js tem os mesmos valores para os campos criados via JS.)
// =========================================================================
const LIM_TITULO  = 100;
const LIM_AUTOR   = 60;
const LIM_RESUMO  = 1000;
const LIM_MATERIA = 30;
const LIM_TOPICO  = 100;

// Verifica se já existe um registro (usado para avisar sobre duplicados)
function jaExiste($conn, $sql, $tipos, ...$params) {
    $st = $conn->prepare($sql);
    $st->bind_param($tipos, ...$params);
    $st->execute();
    $existe = $st->get_result()->num_rows > 0;
    $st->close();
    return $existe;
}

// ===================== MATÉRIAS =====================

// Inserção de matéria
if (isset($_POST['add_materia'])) {
    $nome = trim($_POST['nome_materia'] ?? '');
    if (mb_strlen($nome) > LIM_MATERIA) {
        $msg = "❌ O nome da matéria deve ter no máximo " . LIM_MATERIA . " caracteres.";
    } elseif ($nome !== '') {
        if (jaExiste($conn, "SELECT 1 FROM materia WHERE nome_materia = ?", "s", $nome)) {
            $msg = "⚠️ Não foi possível criar: a matéria '$nome' já existe.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO materia (nome_materia) VALUES (?)");
                $stmt->bind_param("s", $nome);
                $stmt->execute();
                $stmt->close();
                $msg = "✅ Matéria '$nome' adicionada!";
            } catch (mysqli_sql_exception $e) {
                $msg = ($e->getCode() === 1062)
                    ? "⚠️ Não foi possível criar: a matéria '$nome' já existe."
                    : "❌ Erro ao adicionar matéria.";
            }
        }
    }
}

// Edição de matéria
if (isset($_POST['edit_materia'])) {
    $id_edit   = (int) ($_POST['id_materia'] ?? 0);
    $nome_edit = trim($_POST['novo_nome'] ?? '');
    if (mb_strlen($nome_edit) > LIM_MATERIA) {
        $msg = "❌ O nome da matéria deve ter no máximo " . LIM_MATERIA . " caracteres.";
    } elseif ($nome_edit !== '' && $id_edit > 0) {
        if (jaExiste($conn, "SELECT 1 FROM materia WHERE nome_materia = ? AND id_materia <> ?", "si", $nome_edit, $id_edit)) {
            $msg = "⚠️ Não foi possível salvar: já existe uma matéria chamada '$nome_edit'.";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE materia SET nome_materia = ? WHERE id_materia = ?");
                $stmt->bind_param("si", $nome_edit, $id_edit);
                $stmt->execute();
                $stmt->close();
                $msg = "📝 Matéria atualizada com sucesso!";
            } catch (mysqli_sql_exception $e) {
                $msg = ($e->getCode() === 1062)
                    ? "⚠️ Não foi possível salvar: já existe uma matéria chamada '$nome_edit'."
                    : "❌ Erro ao editar matéria.";
            }
        }
    }
}

// Exclusão de matéria
if (isset($_GET['del'])) {
    $id_del = (int) $_GET['del'];
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

// ===================== TÓPICOS =====================

// Inserção de tópico
if (isset($_POST['add_topico'])) {
    $id_materia_topico = (int) ($_POST['id_materia'] ?? 0);
    $nome_topico       = trim($_POST['nome_topico'] ?? '');
    if (mb_strlen($nome_topico) > LIM_TOPICO) {
        $msg = "❌ O tópico deve ter no máximo " . LIM_TOPICO . " caracteres.";
    } elseif ($nome_topico !== '' && $id_materia_topico > 0) {
        if (jaExiste($conn, "SELECT 1 FROM materia_topico WHERE id_materia = ? AND nome_topico = ?", "is", $id_materia_topico, $nome_topico)) {
            $msg = "⚠️ Não foi possível criar: o tópico '$nome_topico' já existe nesta matéria.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO materia_topico (id_materia, nome_topico) VALUES (?, ?)");
                $stmt->bind_param("is", $id_materia_topico, $nome_topico);
                $stmt->execute();
                $stmt->close();
                $msg = "📌 Tópico adicionado com sucesso!";
            } catch (mysqli_sql_exception $e) {
                $msg = ($e->getCode() === 1062)
                    ? "⚠️ Não foi possível criar: o tópico '$nome_topico' já existe nesta matéria."
                    : "❌ Erro ao adicionar tópico.";
            }
        }
    }
}

// Edição de tópico
if (isset($_POST['edit_topico'])) {
    $id_topico        = (int) ($_POST['id_topico'] ?? 0);
    $novo_nome_topico = trim($_POST['novo_nome_topico'] ?? '');
    if (mb_strlen($novo_nome_topico) > LIM_TOPICO) {
        $msg = "❌ O tópico deve ter no máximo " . LIM_TOPICO . " caracteres.";
    } elseif ($id_topico > 0 && $novo_nome_topico !== '') {
        if (jaExiste(
            $conn,
            "SELECT 1 FROM materia_topico
              WHERE nome_topico = ? AND id_topico <> ?
                AND id_materia = (SELECT id_materia FROM materia_topico WHERE id_topico = ?)",
            "sii", $novo_nome_topico, $id_topico, $id_topico
        )) {
            $msg = "⚠️ Não foi possível salvar: o tópico '$novo_nome_topico' já existe nesta matéria.";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE materia_topico SET nome_topico = ? WHERE id_topico = ?");
                $stmt->bind_param("si", $novo_nome_topico, $id_topico);
                $stmt->execute();
                $stmt->close();
                $msg = "📝 Tópico atualizado com sucesso!";
            } catch (mysqli_sql_exception $e) {
                $msg = ($e->getCode() === 1062)
                    ? "⚠️ Não foi possível salvar: o tópico '$novo_nome_topico' já existe nesta matéria."
                    : "❌ Erro ao editar tópico.";
            }
        }
    }
}

// Exclusão de tópico
if (isset($_GET['del_topico'])) {
    $id_del_topico = (int) $_GET['del_topico'];
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
    } elseif (mb_strlen($titulo) > LIM_TITULO || mb_strlen($autor) > LIM_AUTOR || mb_strlen($resumo) > LIM_RESUMO) {
        $msg = "❌ Limite de caracteres excedido (título: " . LIM_TITULO . ", autor: " . LIM_AUTOR . ", resumo: " . LIM_RESUMO . ").";
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
    } elseif (mb_strlen($titulo) > LIM_TITULO || mb_strlen($autor) > LIM_AUTOR || mb_strlen($resumo) > LIM_RESUMO) {
        $msg = "❌ Limite de caracteres excedido (título: " . LIM_TITULO . ", autor: " . LIM_AUTOR . ", resumo: " . LIM_RESUMO . ").";
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

// ===================== DADOS PARA EXIBIÇÃO =====================

// Livros cadastrados
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

// Matérias existentes
$materias = [];
$res_materias = $conn->query("SELECT * FROM materia ORDER BY nome_materia ASC");
if ($res_materias) {
    while ($row = $res_materias->fetch_assoc()) {
        $materias[] = $row;
    }
}

// Tópicos organizados por id_materia
$topicos = [];
$res_topicos = $conn->query("SELECT * FROM materia_topico ORDER BY nome_topico ASC");
if ($res_topicos) {
    while ($t = $res_topicos->fetch_assoc()) {
        $topicos[$t['id_materia']][] = $t;
    }
}

// Versiona o CSS do banner pela data de modificação (evita cache antigo)
$verBanner = @filemtime(__DIR__ . '/../assets/css/banner.css') ?: time();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Matérias - ADM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_materias.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/banner.css?v=<?= $verBanner ?>">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.webp">

    <!-- SweetAlert2 (alerta de confirmação de exclusão) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25"></script>

    <script>
        (function () {
            if ((localStorage.getItem('site-theme') || 'light') === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
</head>
<body>

<?php include '../interface/header.php'; ?>

<!-- ================= BANNER PRINCIPAL (MODO CLARO E ESCURO · PC / TABLET / CELULAR) ================= -->
<section class="banner">
    <picture class="banner-light">
        <source media="(max-width: 600px)"  srcset="../assets/img/banners/cellphone/administradorCelularClaro.webp">
        <source media="(max-width: 1024px)" srcset="../assets/img/banners/tablet/administradorTabletClaro.webp">
        <img src="../assets/img/banners/pc/administradorClaro.webp" alt="Banner Administrador">
    </picture>
    <picture class="banner-dark">
        <source media="(max-width: 600px)"  srcset="../assets/img/banners/cellphone/administradorCelularEscuro.webp">
        <source media="(max-width: 1024px)" srcset="../assets/img/banners/tablet/administradorTabletEscuro.webp">
        <img src="../assets/img/banners/pc/administradorEscuro.webp" alt="Banner Administrador Escuro">
    </picture>
</section>

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
                <input type="text" name="titulo" class="campo-livro" placeholder="Título da obra" maxlength="<?= LIM_TITULO ?>" required>
                <input type="text" name="autor" class="campo-livro" placeholder="Autor(a)" maxlength="<?= LIM_AUTOR ?>" required>
            </div>
            <textarea name="resumo" class="campo-livro" rows="4" placeholder="Breve resumo da obra" maxlength="<?= LIM_RESUMO ?>" required></textarea>

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
                                    <a href="?del_livro=<?= (int)$l['id_livro'] ?>" class="btn-table-action btn-table-del js-confirmar-exclusao"
                                       data-titulo="Excluir livro?"
                                       data-texto="<?= htmlspecialchars('O livro "' . $l['titulo'] . '" será removido. Favoritos e progresso dos usuários nele também serão apagados.', ENT_QUOTES, 'UTF-8') ?>"
                                       title="Excluir">×</a>
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
                    <input type="text" name="titulo" class="campo-livro" maxlength="<?= LIM_TITULO ?>" required>
                    <input type="text" name="autor" class="campo-livro" maxlength="<?= LIM_AUTOR ?>" required>
                </div>
                <textarea name="resumo" class="campo-livro" rows="6" maxlength="<?= LIM_RESUMO ?>" required></textarea>

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

        <!-- Campo de pesquisa -->
        <div class="search-container" style="margin-bottom: 15px;">
            <input type="text" id="campoBuscaMateria" class="input-materia" placeholder="🔍 Pesquisar matéria ou tópico (ex: Química, Estequiometria)..." onkeyup="filtrarMateriasETopicos()">
        </div>

        <form method="POST" class="input-group-append">
            <input type="text" name="nome_materia" class="input-materia" placeholder="Nome da Matéria (ex: Química)" maxlength="<?= LIM_MATERIA ?>" required>
            <button type="submit" name="add_materia" class="btn-adicionar">
                Adicionar <i class="fa-solid fa-plus"></i>
            </button>
        </form>

        <?php if (empty($materias)): ?>
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
            <!-- tabela-materias: rola a partir da 15ª matéria -->
            <div class="table-wrapper tabela-materias">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 55%;">Nome da Matéria</th>
                            <th style="width: 35%; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materias as $m): $id_m = (int) $m['id_materia']; ?>
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

                                    <a href="?del=<?= $id_m ?>" class="btn-table-action btn-table-del js-confirmar-exclusao"
                                       data-titulo="Excluir matéria?"
                                       data-texto="<?= htmlspecialchars('A matéria "' . $m['nome_materia'] . '" e todos os tópicos dela serão removidos.', ENT_QUOTES, 'UTF-8') ?>"
                                       title="Excluir">×</a>
                                </div>
                            </td>
                        </tr>
                        <tr id="painel-topicos-<?= $id_m ?>" class="sub-row-topicos">
                            <td colspan="3">
                                <div class="box-topicos-adm">
                                    <h4>Tópicos de Conteúdo para: <u><?= htmlspecialchars($m['nome_materia']) ?></u></h4>

                                    <ul class="lista-topicos-atuais">
                                        <?php if (isset($topicos[$id_m])): ?>
                                            <?php foreach ($topicos[$id_m] as $t): ?>
                                                <li class="tag-topico-adm" id="tag-topico-<?= (int)$t['id_topico'] ?>">
                                                    <span class="nome-topico-texto"><?= htmlspecialchars($t['nome_topico']) ?></span>
                                                    <i class="fa-solid fa-pen btn-topic-edit" onclick='ativarEdicaoTopico(<?= (int)$t['id_topico'] ?>, <?= htmlspecialchars(json_encode($t['nome_topico']), ENT_QUOTES, 'UTF-8') ?>)' title="Editar Tópico"></i>
                                                    <a href="?del_topico=<?= (int)$t['id_topico'] ?>" class="btn-topic-del js-confirmar-exclusao"
                                                       data-titulo="Excluir tópico?"
                                                       data-texto="<?= htmlspecialchars('O tópico "' . $t['nome_topico'] . '" será removido.', ENT_QUOTES, 'UTF-8') ?>"
                                                       title="Remover Tópico">×</a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Nenhum subtópico adicionado ainda.</span>
                                        <?php endif; ?>
                                    </ul>

                                    <form method="POST" class="form-add-topico-inline">
                                        <input type="hidden" name="id_materia" value="<?= $id_m ?>">
                                        <input type="text" name="nome_topico" placeholder="Adicionar novo tópico (ex: Estequiometria)" maxlength="<?= LIM_TOPICO ?>" required>
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

<!-- ================= NAVEGAÇÃO INFERIOR (tablet e celular) ================= -->
<nav class="bottom-nav" aria-label="Navegação principal">
    <a href="../index.php" class="bn-item">
        <i class="fa-solid fa-house"></i><span>Início</span>
    </a>
    <a href="livros.php" class="bn-item">
        <i class="fa-solid fa-book"></i><span>Biblioteca</span>
    </a>
    <a href="material.php" class="bn-item">
        <i class="fa-solid fa-tv"></i><span>Material</span>
    </a>
    <a href="cronograma.php" class="bn-item">
        <i class="fa-regular fa-calendar-days"></i><span>Cronogramas</span>
    </a>
    <a href="../perfil/configperfil.php" class="bn-item">
        <i class="fa-regular fa-user"></i><span>Perfil</span>
    </a>
</nav>

<?php include '../perfil/vlibras.php'; ?>
<?php include '../interface/footer.php'; ?>

</body>
</html>