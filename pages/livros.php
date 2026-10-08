<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

$id = (int) $_SESSION['id'];

// Confirma que o usuário da sessão ainda existe no banco
$stmt = $conn->prepare("SELECT foto FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$dadosUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$VEST = [
    'fuvest'  => 'Fuvest',
    'unicamp' => 'Unicamp',
    'vunesp'  => 'Vunesp',
    'enem'    => 'Enem',
];

// AJUSTE AQUI: datas das provas para a contagem regressiva (ISO 8601, fuso -03:00)
$PROVAS = [
    ['rotulo' => 'ENEM 2026',    'data' => '2026-11-08T13:30:00-03:00', 'cor' => 'amarelo'],
    ['rotulo' => 'FUVEST 2027',  'data' => '2026-11-22T13:00:00-03:00', 'cor' => 'vermelho'],
    ['rotulo' => 'UNICAMP 2026', 'data' => '2026-10-25T13:30:00-03:00', 'cor' => 'verde'],
    ['rotulo' => 'UNESP 2027',   'data' => '2026-11-29T14:00:00-03:00', 'cor' => 'roxo'],
];

// Livros + vestibulares + progresso e favorito do usuário logado
$sql = "SELECT l.id_livro, l.titulo, l.autor, l.resumo, l.capa,
               GROUP_CONCAT(DISTINCT lv.vestibular) AS vests,
               (SELECT p.status FROM livro_progresso p
                 WHERE p.id_livro = l.id_livro AND p.id_usuario = ?) AS status,
               EXISTS(SELECT 1 FROM livro_favorito f
                 WHERE f.id_livro = l.id_livro AND f.id_usuario = ?) AS fav
        FROM livro l
        LEFT JOIN livro_vestibular lv ON lv.id_livro = l.id_livro
        GROUP BY l.id_livro
        ORDER BY l.titulo ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $id);
$stmt->execute();
$livros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Últimos vídeos favoritos (barra lateral)
$stmt = $conn->prepare("SELECT video_id, titulo_video FROM favoritos WHERE id_usuario = ? ORDER BY id_favorito DESC LIMIT 2");
$stmt->bind_param("i", $id);
$stmt->execute();
$videosFav = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca – Leituras obrigatórias</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/livros.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">

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

    <div class="wrapper">

        <!-- ================= BARRA LATERAL ================= -->
        <aside class="sidebar">
            <section class="side-bloco">
                <h2 class="side-titulo">Explore por vestibular:</h2>
                <ul class="filtro-vest">
                    <li><button type="button" class="vest-btn ativo" data-vest-filter="todos"><i class="quad q-todos"></i>Todos</button></li>
                    <li><button type="button" class="vest-btn" data-vest-filter="fuvest"><i class="quad q-fuvest"></i>Fuvest</button></li>
                    <li><button type="button" class="vest-btn" data-vest-filter="unicamp"><i class="quad q-unicamp"></i>Unicamp</button></li>
                    <li><button type="button" class="vest-btn" data-vest-filter="vunesp"><i class="quad q-vunesp"></i>Vunesp</button></li>
                    <li><button type="button" class="vest-btn" data-vest-filter="enem"><i class="quad q-enem"></i>Enem</button></li>
                </ul>
            </section>

            <section class="side-bloco">
                <h2 class="side-titulo">Contagem regressiva VESTIBULARES</h2>
                <ul class="contagem">
                    <?php foreach ($PROVAS as $p): ?>
                        <li class="prova prova-<?= e($p['cor']) ?>">
                            <span class="prova-nome"><?= e($p['rotulo']) ?></span>
                            <span class="prova-tempo" data-alvo="<?= e($p['data']) ?>">--</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="side-bloco">
                <h2 class="side-titulo">Status de leitura</h2>
                <ul class="filtro-status">
                    <li><button type="button" class="status-btn ativo" data-status-filter="todas"><i class="fa-solid fa-book-open ic-todas"></i>Todas as obras</button></li>
                    <li><button type="button" class="status-btn" data-status-filter="andamento"><i class="fa-regular fa-clock ic-and"></i>Em andamento</button></li>
                    <li><button type="button" class="status-btn" data-status-filter="concluidas"><i class="fa-solid fa-circle-check ic-ok"></i>Concluídas</button></li>
                    <li><button type="button" class="status-btn" data-status-filter="nao_iniciadas"><i class="fa-regular fa-circle ic-nao"></i>Não iniciadas</button></li>
                    <li><button type="button" class="status-btn" data-status-filter="favoritas"><i class="fa-solid fa-bookmark ic-fav"></i>Favoritas</button></li>
                </ul>
            </section>

            <section class="side-bloco">
                <div class="side-linha">
                    <h2 class="side-titulo">Meus favoritos</h2>
                    <a class="ver-todos" href="../favoritos.php">Ver todos</a> <!-- AJUSTE o link -->
                </div>
                <?php if (empty($videosFav)): ?>
                    <p class="side-vazio">Nenhum vídeo favoritado ainda.</p>
                <?php else: ?>
                    <?php foreach ($videosFav as $v): ?>
                        <a class="video-fav" href="https://www.youtube.com/watch?v=<?= e($v['video_id']) ?>" target="_blank" rel="noopener">
                            <img src="https://img.youtube.com/vi/<?= e($v['video_id']) ?>/mqdefault.jpg" alt="" loading="lazy">
                            <span><?= e($v['titulo_video'] ?: 'Vídeo') ?></span>
                            <i class="fa-solid fa-bookmark"></i>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </aside>

        <!-- ================= CONTEÚDO ================= -->
        <div class="principalContent">

            <section class="banner">
                <span class="banner-faixa banner-faixa-e"></span>
                <span class="banner-faixa banner-faixa-d"></span>
                <h1 class="banner-titulo">BIBLIOTECA</h1>
                <p class="banner-sub">Clique nas imagens para explorar!</p>
            </section>

            <main class="areaContent">
                <div class="toolbar">
                    <nav class="abas" aria-label="Filtrar por status">
                        <button type="button" class="aba ativo" data-status-filter="todas">Todas as obras</button>
                        <button type="button" class="aba" data-status-filter="andamento">Em andamento</button>
                        <button type="button" class="aba" data-status-filter="concluidas">Concluídas</button>
                        <button type="button" class="aba" data-status-filter="favoritas">Favoritos</button>
                    </nav>
                    <label class="busca">
                        <input type="search" id="busca" placeholder="Busque obra ou autor..." autocomplete="off">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </label>
                </div>

                <div class="lista-obras" id="listaObras">
                    <?php foreach ($livros as $l):
                        $status = $l['status'] ?? '';
                        $fav    = !empty($l['fav']);
                        $vl     = $l['vests'] ? explode(',', $l['vests']) : [];
                    ?>
                    <article class="livro-card <?= $status ? 'st-' . e($status) : '' ?> <?= $fav ? 'is-fav' : '' ?>"
                             tabindex="0" role="button"
                             data-id="<?= (int)$l['id_livro'] ?>"
                             data-status="<?= e($status) ?>"
                             data-fav="<?= $fav ? 1 : 0 ?>"
                             data-vests="<?= e(implode(' ', $vl)) ?>"
                             data-busca="<?= e(mb_strtolower($l['titulo'] . ' ' . $l['autor'], 'UTF-8')) ?>">

                        <div class="livro-capa">
                            <?php if (!empty($l['capa'])): ?>
                                <img src="../<?= e($l['capa']) ?>" alt="Capa de <?= e($l['titulo']) ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fa-solid fa-book"></i>
                            <?php endif; ?>
                        </div>

                        <div class="livro-info">
                            <h3 class="livro-titulo"><?= e($l['titulo']) ?></h3>
                            <p class="livro-autor"><?= e($l['autor']) ?></p>
                            <p class="livro-desc"><?= e($l['resumo']) ?></p>
                            <div class="livro-tags">
                                <?php foreach ($VEST as $k => $nome): if (in_array($k, $vl, true)): ?>
                                    <span class="tag tag-<?= $k ?>"><?= $nome ?></span>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>

                        <div class="livro-indicadores">
                            <button type="button" class="btn-fav" aria-label="Favoritar obra" title="Favoritar">
                                <i class="fa-solid fa-bookmark"></i>
                            </button>
                            <i class="fa-regular fa-clock ico-and" title="Em andamento"></i>
                            <i class="fa-solid fa-circle-check ico-ok" title="Concluída"></i>
                        </div>

                        <div class="livro-resumo" hidden><?= nl2br(e($l['resumo'])) ?></div>
                    </article>
                    <?php endforeach; ?>

                    <p class="sem-resultados" id="semResultados" <?= empty($livros) ? '' : 'hidden' ?>>
                        <?= empty($livros) ? 'Nenhuma obra cadastrada ainda.' : 'Nenhuma obra encontrada com esses filtros.' ?>
                    </p>
                </div>
            </main>
        </div>
    </div>

    <!-- ================= MODAL DA OBRA ================= -->
    <dialog id="modalLivro" class="modal-livro">
        <button type="button" class="close-button" aria-label="Fechar">&times;</button>
        <div class="modal-corpo">
            <div class="modal-capa" id="mCapa"></div>
            <div class="modal-texto">
                <h2 id="mTitulo"></h2>
                <p class="modal-autor" id="mAutor"></p>
                <div class="livro-tags" id="mTags"></div>
                <div class="modal-resumo" id="mResumo"></div>
            </div>
        </div>
        <div class="modal-acoes">
            <div class="seg" role="group" aria-label="Seu progresso">
                <button type="button" data-set-status="nao_iniciada"><i class="fa-regular fa-circle"></i> Não iniciada</button>
                <button type="button" data-set-status="em_andamento"><i class="fa-regular fa-clock"></i> Em andamento</button>
                <button type="button" data-set-status="concluida"><i class="fa-solid fa-circle-check"></i> Concluída</button>
            </div>
            <button type="button" class="btn-fav-modal" id="mFav"><i class="fa-solid fa-bookmark"></i> <span>Favoritar</span></button>
        </div>
        <p class="modal-erro" id="mErro" hidden>Não foi possível salvar. Tente novamente.</p>
    </dialog>

    <script src="../assets/js/lib/livros.js" defer></script>

<?php include '../perfil/vlibras.php'; ?>

</body>
</html>