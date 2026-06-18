<?php
session_start();

// 1. Verifica se a variável de sessão existe e se o usuário está logado
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$id = intval($_SESSION['id']);
$res = $conn->query("SELECT foto FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');
$hoje = date('Y-m-d');

// CONEXÃO COM O BANCO (PDO)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=phronesis;charset=utf8", "root", "Home@spSENAI2025!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro banco: " . $e->getMessage());
}

// Busca matérias para o formulário
$sql = "SELECT id_materia, nome_materia FROM materia ORDER BY nome_materia ASC";
$consulta = $pdo->query($sql);
$lista_materias_db = $consulta->fetchAll(PDO::FETCH_ASSOC);

$materias_com_conteudos = [];
try {
    $sql_conteudos = "SELECT m.nome_materia, t.nome_topico AS nome_conteudo
                      FROM materia m
                      LEFT JOIN materia_topico t ON m.id_materia = t.id_materia 
                      ORDER BY m.nome_materia ASC, t.nome_topico ASC";
    $stmt_cont = $pdo->query($sql_conteudos);
    $lista_completa = $stmt_cont->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($lista_completa) || (count($lista_completa) === 1 && empty($lista_completa[0]['nome_conteudo']))) {
        $sql_conteudos = "SELECT m.nome_materia, c.nome_conteudo 
                          FROM materia m
                          LEFT JOIN conteudo c ON m.id_materia = c.id_materia 
                          ORDER BY m.nome_materia ASC, c.nome_conteudo ASC";
        $stmt_cont = $pdo->query($sql_conteudos);
        $lista_completa = $stmt_cont->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($lista_completa as $linha) {
        $mat = $linha['nome_materia'];
        if (!isset($materias_com_conteudos[$mat])) {
            $materias_com_conteudos[$mat] = [];
        }
        if (!empty($linha['nome_conteudo'])) {
            $materias_com_conteudos[$mat][] = $linha['nome_conteudo'];
        }
    }
} catch (Exception $e) {
    $consulta_fallback = $pdo->query("SELECT nome_materia FROM materia ORDER BY nome_materia ASC");
    while ($row = $consulta_fallback->fetch(PDO::FETCH_ASSOC)) {
        $materias_com_conteudos[$row['nome_materia']] = [];
    }
}

// ---- BUSCA CRONOGRAMAS DIRETAMENTE DO BANCO DE DADOS (Persistência) ----
$stmtCronos = $pdo->prepare("SELECT id_cronograma, titulo_cronograma AS titulo, google_calendar_id AS calendar_id, DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') AS data FROM cronograma WHERE id_usuario = ? ORDER BY id_cronograma DESC");
$stmtCronos->execute([$id]);
$meus_cronogramas_db = $stmtCronos->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['logout'])) {
    $conn->query("UPDATE usuario SET google_token = NULL WHERE id_usuario = $id");
    unset($_SESSION['upload_token'], $_SESSION['temp_cronograma'], $_SESSION['editando_id']);
    header('Location: meu_calendario.php');
    exit;
}

if (!isset($_SESSION['upload_token'])) {
    header('Location: meu_calendario.php');
    exit;
}

// Configuração Google
$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->setAccessToken($_SESSION['upload_token']);
$service = new Google\Service\Calendar($client);

$msg_feedback = "";
$id_visualizacao = $_GET['ver'] ?? null;
$mostrar_modal = false;

$calendar_id_exibir = null;
if ($id_visualizacao !== null) {
    $stmtVis = $pdo->prepare("SELECT google_calendar_id, titulo_cronograma FROM cronograma WHERE id_cronograma = ? AND id_usuario = ?");
    $stmtVis->execute([$id_visualizacao, $id]);
    $crono_ver = $stmtVis->fetch(PDO::FETCH_ASSOC);
    if ($crono_ver) {
        $calendar_id_exibir = $crono_ver['google_calendar_id'];
        $titulo_exibir = $crono_ver['titulo_cronograma'];
    }
}

// --- EXCLUSÃO ---
if (isset($_GET['deletar_salvo'])) {
    $id_del = intval($_GET['deletar_salvo']);
    
    $stmtCheck = $pdo->prepare("SELECT google_calendar_id FROM cronograma WHERE id_cronograma = ? AND id_usuario = ?");
    $stmtCheck->execute([$id_del, $id]);
    $crono_del = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($crono_del) {
        $calendarId = $crono_del['google_calendar_id'] ?? null;
        try {
            if ($calendarId && $calendarId !== 'primary') {
                $service->calendars->delete($calendarId);
            }
            $stmtDel = $pdo->prepare("DELETE FROM `cronograma` WHERE `id_cronograma` = ? AND `id_usuario` = ?");
            $stmtDel->execute([$id_del, $id]);
            $msg_feedback = "🗑️ Cronograma removido com sucesso!";
        } catch (Exception $e) {
            $msg_feedback = "⚠️ Erro ao remover: " . $e->getMessage();
        }
    }
    header('Location: cronograma.php?msg=' . urlencode($msg_feedback));
    exit;
}

// Prepara edição
$dados_edicao = null;
if (isset($_GET['editar'])) {
    $id_ed = intval($_GET['editar']);
    $stmtEd = $pdo->prepare("SELECT google_calendar_id, titulo_cronograma FROM cronograma WHERE id_cronograma = ? AND id_usuario = ?");
    $stmtEd->execute([$id_ed, $id]);
    $crono_ed = $stmtEd->fetch(PDO::FETCH_ASSOC);
    if ($crono_ed) {
        $_SESSION['editando_id'] = $id_ed;
        $id_visualizacao = null;
    }
}

if (isset($_GET['msg'])) $msg_feedback = $_GET['msg'];

// --- GERAÇÃO TEMPORÁRIA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $materias_selecionadas = $_POST['materias_id'] ?? [];
    $dias_permitidos = $_POST['dias_semana'] ?? [];
    
    $intervalo_seguranca = $data_inicio->diff($data_fim);

    if (empty($materias_selecionadas)) {
        $msg_feedback = "❌ Erro: Selecione ao menos uma matéria.";
    } elseif ($data_fim < $data_inicio) {
        $msg_feedback = "❌ Erro: Data final não pode ser menor que a inicial.";
    } elseif ($intervalo_seguranca->days > 366) { 
        $msg_feedback = "❌ Erro: O período máximo permitido para um cronograma é de 1 ano.";
    } else {
        $_SESSION['ultimo_nome'] = !empty($_POST['nome_cronograma']) ? $_POST['nome_cronograma'] : "Plano de Estudos";
        $nomes_map = [];
        foreach ($lista_materias_db as $mat) {
            if (in_array($mat['id_materia'], $materias_selecionadas)) {
                $nomes_map[$mat['id_materia']] = $mat['nome_materia'];
            }
        }

        $cronograma_gerado = [];
        $data_atual = clone $data_inicio;
        $intervalo = $data_inicio->diff($data_fim);

        for ($i = 0; $i <= $intervalo->days; $i++) {
            if (in_array($data_atual->format('N'), $dias_permitidos)) {
                $horario_acumulado = new DateTime($data_atual->format('Y-m-d') . ' ' . $_POST['hora_inicio']);

                foreach ($materias_selecionadas as $id_da_vez) {
                    $inicio_sessao = clone $horario_acumulado;
                    $fim_sessao = clone $inicio_sessao;
                    $fim_sessao->modify('+' . $_POST['horas_estudo'] . ' hours');

                    $cronograma_gerado[] = [
                        'id_materia' => $id_da_vez,
                        'materia' => $nomes_map[$id_da_vez],
                        'inicio_iso' => $inicio_sessao->format('Y-m-d\TH:i:s'),
                        'fim_iso' => $fim_sessao->format('Y-m-d\TH:i:s')
                    ];

                    $horario_acumulado->modify('+' . $_POST['horas_estudo'] . ' hours');
                }
            }
            $data_atual->modify('+1 day');
        }
        $_SESSION['temp_cronograma'] = $cronograma_gerado;
        $_SESSION['temp_params'] = $_POST;
        $mostrar_modal = true;
    }
}

// --- SALVAR ---
if (isset($_POST['sincronizar']) && isset($_SESSION['temp_cronograma'])) {
    set_time_limit(300);
    try {
        $pdo->beginTransaction();
        $id_usuario_logado = $id;
        $calendarIdParaUso = null;
        $id_cronograma_existente = null;

        if (isset($_SESSION['editando_id'])) {
            $id_cronograma_existente = intval($_SESSION['editando_id']);
            $stmt = $pdo->prepare("SELECT google_calendar_id FROM cronograma WHERE id_cronograma = ? AND id_usuario = ?");
            $stmt->execute([$id_cronograma_existente, $id]);
            $calendarIdParaUso = $stmt->fetchColumn();
        }

        if ($id_cronograma_existente && $calendarIdParaUso) {
            $stmtUpd = $pdo->prepare("UPDATE `cronograma` SET `titulo_cronograma` = ? WHERE `id_cronograma` = ? AND `id_usuario` = ?");
            $stmtUpd->execute([$_SESSION['ultimo_nome'], $id_cronograma_existente, $id]);

            $pdo->prepare("DELETE FROM `cronograma_item` WHERE `id_cronograma` = ?")->execute([$id_cronograma_existente]);
            $id_cronograma_db = $id_cronograma_existente;

            try {
                $events = $service->events->listEvents($calendarIdParaUso);
                foreach ($events->getItems() as $event) {
                    $service->events->delete($calendarIdParaUso, $event->getId());
                }
            } catch (Exception $e) {}
        } else {
            $newCalendar = new Google\Service\Calendar\Calendar();
            $newCalendar->setSummary('Phronesis: ' . $_SESSION['ultimo_nome']);
            $newCalendar->setTimeZone('America/Sao_Paulo');
            $createdCalendar = $service->calendars->insert($newCalendar);
            $calendarIdParaUso = $createdCalendar->getId();

            $stmtCrono = $pdo->prepare("INSERT INTO `cronograma` (`id_usuario`, `titulo_cronograma`, `google_calendar_id`, `data_criacao`) VALUES (?, ?, ?, NOW())");
            $stmtCrono->execute([$id_usuario_logado, $_SESSION['ultimo_nome'], $calendarIdParaUso]);
            $id_cronograma_db = $pdo->lastInsertId();
        }

        foreach ($_SESSION['temp_cronograma'] as $evento) {
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Estudo: ' . $evento['materia'],
                'start' => ['dateTime' => $evento['inicio_iso'], 'timeZone' => 'America/Sao_Paulo'],
                'end'   => ['dateTime' => $evento['fim_iso'], 'timeZone' => 'America/Sao_Paulo'],
            ]);
            $google_event = $service->events->insert($calendarIdParaUso, $event);

            $stmtItem = $pdo->prepare("INSERT INTO `cronograma_item` (`id_cronograma`, `id_materia`, `google_event_id`, `data_estudo`, `data_fim`) VALUES (?, ?, ?, ?, ?)");
            $stmtItem->execute([
                $id_cronograma_db,
                $evento['id_materia'],
                $google_event->getId(),
                str_replace('T', ' ', $evento['inicio_iso']),
                str_replace('T', ' ', $evento['fim_iso'])
            ]);
        }

        $pdo->commit();

        $idx_final = $id_cronograma_db;
        unset($_SESSION['editando_id'], $_SESSION['temp_cronograma'], $_SESSION['temp_params']);
        
        header('Location: cronograma.php?ver=' . $idx_final . '&msg=' . urlencode("✅ Cronograma salvo com sucesso!"));
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg_feedback = "❌ Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis - Cronograma</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/cronograma.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .history-list {
            max-height: 480px; 
            overflow-y: auto;
            padding-right: 4px;
        }

        .history-list::-webkit-scrollbar,
        .conteiner-materias-expansivel::-webkit-scrollbar {
            width: 6px;
        }
        .history-list::-webkit-scrollbar-track,
        .conteiner-materias-expansivel::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        .history-list::-webkit-scrollbar-thumb,
        .conteiner-materias-expansivel::-webkit-scrollbar-thumb {
            background: rgba(124, 58, 237, 0.3);
            border-radius: 10px;
        }
        .history-list::-webkit-scrollbar-thumb:hover,
        .conteiner-materias-expansivel::-webkit-scrollbar-thumb:hover {
            background: rgba(124, 58, 237, 0.6);
        }

        .btn-toggle-materias {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            border: none;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(124, 58, 237, 0.2);
        }
        .btn-toggle-materias:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .conteiner-materias-expansivel {
            display: none; 
            margin-top: 15px;
            flex-direction: column;
            gap: 10px;
            animation: fadeIn 0.3s ease;
            max-height: 280px; 
            overflow-y: auto;  
            padding-right: 4px;
        }

        .materia-accordion-item {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0; 
        }
        .dark-theme .materia-accordion-item {
            background-color: #26263b;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .materia-accordion-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
            font-weight: 500;
            color: #fff;
            background-color: rgba(255, 255, 255, 0.02);
            transition: background 0.2s;
        }
        .materia-accordion-header:hover {
            background-color: rgba(255, 255, 255, 0.07);
        }
        .materia-accordion-header i.fa-folder {
            margin-right: 10px;
            color: #a78bfa;
        }
        .materia-accordion-header i.chevron {
            transition: transform 0.3s ease;
        }
        .materia-accordion-item.active .materia-accordion-header i.chevron {
            transform: rotate(180deg);
        }
        .materia-accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background-color: rgba(0, 0, 0, 0.15);
        }
        .materia-accordion-content ul {
            list-style: none;
            padding: 12px 16px;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .materia-accordion-content li {
            font-size: 0.9rem;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .materia-accordion-content li::before {
            content: "•";
            color: #7c3aed;
            font-size: 1.2rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dia-desabilitado {
            opacity: 0.4;
            pointer-events: none;
        }
    </style>
</head>

<body>

    <?php include '../interface/header.php'; ?>

    <div id="confirmModal" class="modal" style="<?= $mostrar_modal ? 'display:flex;' : '' ?>">
        <div class="modal-content">
            <h3>📚 Confirmar Cronograma</h3>
            <p>Título: <b><?= htmlspecialchars($_SESSION['ultimo_nome'] ?? '') ?></b></p>
            <form method="POST">
                <button type="submit" name="sincronizar" class="btn-sync">Confirmar e Salvar</button>
                <button type="button" onclick="window.location.href='cronograma.php'" class="btn-cancel-modal">Cancelar</button>
            </form>
        </div>
    </div>

    <div id="loadingScreen" class="modal" style="display: none;">
        <div class="modal-content loader-box">
            <h3 id="loadingText">🗓️ Sincronizando com o Google Agenda...</h3>
            <p id="loadingSubtext">Isso pode levar alguns segundos dependendo do tamanho do seu cronograma.</p>
            <div class="progress-bar-container">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
    </div>

    <main class="main-layout">

        <section class="container-principal">
            <?php if ($msg_feedback): ?>
                <div class="alert-message"><?= $msg_feedback ?></div>
            <?php endif; ?>

            <?php if ($calendar_id_exibir && !isset($_GET['editar'])): ?>
                <div class="agenda-header">
                    <h3><i class="fa-solid fa-calendar-days"></i> Agenda: <?= htmlspecialchars($titulo_exibir) ?></h3>
                    <a href="cronograma.php" class="btn-novo-crono">+ Novo Cronograma</a>
                </div>
                <iframe src="https://calendar.google.com/calendar/embed?src=<?= urlencode($calendar_id_exibir) ?>&ctz=America%2FSao_Paulo&wkst=1&bgcolor=%23fbf9ff&color=%237c3aed&showCalendars=0&showTabs=1" class="calendar-embed" style="border: none;" allowfullscreen></iframe>
            <?php else: ?>

                <div class="card-header">
                    <div class="title-left">
                        <div class="icon-purple-box">
                            <i class="fa-regular fa-calendar-plus"></i>
                        </div>
                        <div>
                            <h2>Phronesis</h2>
                            <p>Crie e organize seu cronograma de estudos</p>
                        </div>
                    </div>
                    <a href="?logout=1" class="btn-desconectar">
                        Desconectar <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>

                <form method="POST" id="formCronograma" class="form-grid">
                    <div class="full-width field-group">
                        <label>Nome do seu Cronograma:</label>
                        <input type="text" name="nome_cronograma" placeholder="Ex: Maratona ENEM 2026"
                            value="<?= htmlspecialchars($_POST['nome_cronograma'] ?? '') ?>" required>
                    </div>

                    <div class="field-group input-icon-wrapper">
                        <label>Data Início:</label>
                        <div class="input-with-icon">
                            <input type="date" name="data_inicio" id="data_inicio" min="<?= $hoje ?>" value="<?= $_POST['data_inicio'] ?? $hoje ?>" onchange="atualizarDiasSemana()" required>
                            <i class="fa-regular fa-calendar input-icon-right"></i>
                        </div>
                    </div>

                    <div class="field-group input-icon-wrapper">
                        <label>Data Fim:</label>
                        <div class="input-with-icon">
                            <input type="date" name="data_fim" id="data_fim" min="<?= $hoje ?>" value="<?= $_POST['data_fim'] ?? '' ?>" onchange="atualizarDiasSemana()" required>
                            <i class="fa-regular fa-calendar input-icon-right"></i>
                        </div>
                    </div>

                    <div class="full-width field-group">
                        <label>Dias da Semana:</label>
                        <div class="list-container scrolling-list" id="containerDiasSemana">
                            </div>
                    </div>

                    <div class="field-group input-icon-wrapper">
                        <label>Hora Início:</label>
                        <div class="input-with-icon">
                            <input type="time" name="hora_inicio" value="<?= $_POST['hora_inicio'] ?? '08:00' ?>" required>
                            <i class="fa-regular fa-clock input-icon-right"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Duração (horas):</label>
                        <input type="number" name="horas_estudo" value="<?= $_POST['horas_estudo'] ?? '2' ?>" required>
                    </div>

                    <div class="full-width field-group">
                        <label>Matérias Disponíveis:</label>
                        <div class="list-container scrolling-list">
                            <?php
                            $mats_ativas = $_POST['materias_id'] ?? [];
                            if (empty($lista_materias_db)): ?>
                                <div class="row-item empty-state-select">Selecione as matérias que deseja estudar...</div>
                            <?php else:
                                foreach ($lista_materias_db as $m): ?>
                                    <label class="row-item">
                                        <span><?= htmlspecialchars($m['nome_materia']) ?></span>
                                        <input type="checkbox" name="materias_id[]" value="<?= $m['id_materia'] ?>"
                                            <?= in_array($m['id_materia'], $mats_ativas) ? 'checked' : '' ?> class="custom-checkbox">
                                    </label>
                            <?php endforeach;
                            endif; ?>
                        </div>
                    </div>

                    <button type="submit" name="gerar" class="btn-gen full-width">
                        <i class="fa-regular fa-calendar-check"></i> <?= isset($_GET['editar']) ? 'Regerar e Atualizar' : 'Gerar Cronograma' ?>
                    </button>
                </form>
            <?php endif; ?>
        </section>

        <aside class="sidebar-historico">
            <div class="sidebar-header">
                <h3><i class="fa-regular fa-clock"></i> Histórico</h3>
            </div>

            <?php if (empty($meus_cronogramas_db)): ?>
                <div class="empty-history-wrapper">
                    <div class="empty-illustration">
                        <div class="book-purple-icon">
                            <i class="fa-solid fa-book-open"></i>
                            <span class="sparkle-1">✦</span>
                            <span class="sparkle-2">✦</span>
                        </div>
                    </div>
                    <h4>Nenhum cronograma salvo.</h4>
                    <p>Crie seu primeiro cronograma para vê-lo aqui!</p>
                </div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach ($meus_cronogramas_db as $cron): ?>
                        <div class="cronograma-item">
                            <a href="?ver=<?= $cron['id_cronograma'] ?>" class="crono-link">
                                <span class="crono-title"><?= htmlspecialchars($cron['titulo']) ?></span>
                                <span class="crono-date"><?= $cron['data'] ?></span>
                            </a>
                            <div class="cronograma-actions">
                                <a href="?editar=<?= $cron['id_cronograma'] ?>" class="btn-action btn-edit" title="Editar" onclick="dispararLoadingEdicao(event, this.href)"><i class="fa-solid fa-pen"></i></a>
                                <a href="#" class="btn-action btn-del" title="Excluir" onclick="confirmDelete(event, '<?= $cron['id_cronograma'] ?>')"><i class="fa-solid fa-xmark"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!($calendar_id_exibir && !isset($_GET['editar']))): ?>
                <button class="btn-toggle-materias" id="btnToggleMaterias">
                    <span>Navegar Conteúdos por Matéria</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>

                <div class="conteiner-materias-expansivel" id="conteinerMaterias">
                    <?php if (!empty($materias_com_conteudos)): ?>
                        <?php foreach ($materias_com_conteudos as $materia_nome => $conteudos): ?>
                            <div class="materia-accordion-item">
                                <div class="materia-accordion-header">
                                    <span><i class="fa-regular fa-folder"></i> <?= htmlspecialchars($materia_nome) ?></span>
                                    <i class="fa-solid fa-chevron-down chevron"></i>
                                </div>
                                <div class="materia-accordion-content">
                                    <ul>
                                        <?php if (!empty($conteudos)): ?>
                                            <?php foreach ($conteudos as $conteudo): ?>
                                                <li><?= htmlspecialchars($conteudo) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li style="color: #8e8ea8; font-style: italic;">Nenhum conteúdo cadastrado para esta matéria ainda.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #8e8ea8; font-size: 0.9rem; text-align: center; margin-top: 10px;">Nenhuma matéria carregada do banco.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    </main>

    <div id="deleteConfirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>🗑️ Excluir Cronograma</h3>
        <p>Título: <b id="deleteCronoTitle"></b></p>
        <p>Isso removerá a agenda criada no Google Agenda definitivamente!</p>
        <div class="modal-actions-custom">
            <button type="button" id="btnConfirmarExclusaoFinal" class="btn-del-modal">Sim, excluir!</button>
            <button type="button" onclick="fecharModalExclusao()" class="btn-cancel-modal">Cancelar</button>
        </div>
    </div>
</div>


    <script src="../assets/js/lib/cronograma.js" defer></script>
</body>
</html>