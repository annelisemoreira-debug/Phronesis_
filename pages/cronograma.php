<?php
session_start();

// 1. Verifica se a variável de sessão existe e se o usuário está logado
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

// --- CONEXÃO ---
$conn = new mysqli("localhost", "root", "Home@spSENAI2025!", "phronesis");

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// 2. Verifica se o ID da sessão ainda é válido no banco de dados (Segurança contra SQL Injection)
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

if (!isset($_SESSION['meus_cronogramas'])) {
    $_SESSION['meus_cronogramas'] = [];
}

// Logout e Limpeza
if (isset($_GET['logout'])) {
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
if ($id_visualizacao !== null && isset($_SESSION['meus_cronogramas'][$id_visualizacao])) {
    $calendar_id_exibir = $_SESSION['meus_cronogramas'][$id_visualizacao]['calendar_id'];
}

// --- EXCLUSÃO ---
if (isset($_GET['deletar_salvo'])) {
    $id_del = $_GET['deletar_salvo'];
    if (isset($_SESSION['meus_cronogramas'][$id_del])) {
        $calendarId = $_SESSION['meus_cronogramas'][$id_del]['calendar_id'] ?? null;
        try {
            if ($calendarId && $calendarId !== 'primary') {
                $service->calendars->delete($calendarId);
            }
            $stmtDel = $pdo->prepare("DELETE FROM `cronograma` WHERE `google_calendar_id` = ?");
            $stmtDel->execute([$calendarId]);
            $msg_feedback = "🗑️ Cronograma removido com sucesso!";
        } catch (Exception $e) { 
            $msg_feedback = "⚠️ Erro ao remover: " . $e->getMessage(); 
        }
        unset($_SESSION['meus_cronogramas'][$id_del]);
    }
    header('Location: cronograma.php?msg=' . urlencode($msg_feedback));
    exit;
}

// Prepara edição
$dados_edicao = null;
if (isset($_GET['editar'])) {
    $id_ed = $_GET['editar'];
    if (isset($_SESSION['meus_cronogramas'][$id_ed])) {
        $dados_edicao = $_SESSION['meus_cronogramas'][$id_ed]['params'] ?? null;
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

    if (empty($materias_selecionadas)) {
        $msg_feedback = "❌ Erro: Selecione ao menos uma matéria.";
    } elseif ($data_fim < $data_inicio) {
        $msg_feedback = "❌ Erro: Data final não pode ser menor que a inicial.";
    } else {
        $_SESSION['ultimo_nome'] = !empty($_POST['nome_cronograma']) ? $_POST['nome_cronograma'] : "Plano de Estudos";
        $nomes_map = [];
        foreach($lista_materias_db as $mat) {
            if(in_array($mat['id_materia'], $materias_selecionadas)) {
                $nomes_map[$mat['id_materia']] = $mat['nome_materia'];
            }
        }

        $cronograma_gerado = [];
        $data_atual = clone $data_inicio;
        $materia_index = 0;
        $intervalo = $data_inicio->diff($data_fim);

        for ($i = 0; $i <= $intervalo->days; $i++) {
            if (in_array($data_atual->format('N'), $dias_permitidos)) {
                $inicio_sessao = new DateTime($data_atual->format('Y-m-d') . ' ' . $_POST['hora_inicio']);
                $fim_sessao = clone $inicio_sessao;
                $fim_sessao->modify('+' . $_POST['horas_estudo'] . ' hours');
                $id_da_vez = $materias_selecionadas[$materia_index % count($materias_selecionadas)];
                $cronograma_gerado[] = [
                    'id_materia' => $id_da_vez,
                    'materia' => $nomes_map[$id_da_vez],
                    'inicio_iso' => $inicio_sessao->format('Y-m-d\TH:i:s'),
                    'fim_iso' => $fim_sessao->format('Y-m-d\TH:i:s')
                ];
                $materia_index++;
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
    try {
        $pdo->beginTransaction(); 
        $id_usuario_logado = $_SESSION['id'] ?? 1;
        $calendarIdParaUso = null;
        $id_cronograma_existente = null;

        if (isset($_SESSION['editando_id'])) {
            $idx = $_SESSION['editando_id'];
            $calendarIdParaUso = $_SESSION['meus_cronogramas'][$idx]['calendar_id'] ?? null;
            
            $stmt = $pdo->prepare("SELECT id_cronograma FROM cronograma WHERE google_calendar_id = ?");
            $stmt->execute([$calendarIdParaUso]);
            $id_cronograma_existente = $stmt->fetchColumn();
        }

        if ($id_cronograma_existente) {
            $stmtUpd = $pdo->prepare("UPDATE `cronograma` SET `titulo_cronograma` = ? WHERE `id_cronograma` = ?");
            $stmtUpd->execute([$_SESSION['ultimo_nome'], $id_cronograma_existente]);
            
            $pdo->prepare("DELETE FROM `cronograma_item` WHERE `id_cronograma` = ?")->execute([$id_cronograma_existente]);
            $id_cronograma_db = $id_cronograma_existente;

            try {
                $events = $service->events->listEvents($calendarIdParaUso);
                foreach ($events->getItems() as $event) { $service->events->delete($calendarIdParaUso, $event->getId()); }
            } catch (Exception $e) {}

        } else {
            $newCalendar = new Google\Service\Calendar\Calendar();
            $newCalendar->setSummary('Phronesis: ' . $_SESSION['ultimo_nome']);
            $newCalendar->setTimeZone('America/Sao_Paulo');
            $createdCalendar = $service->calendars->insert($newCalendar);
            $calendarIdParaUso = $createdCalendar->getId();

            $stmtCrono = $pdo->prepare("INSERT INTO `cronograma` (`id_usuario`, `titulo_cronograma`, `google_calendar_id`) VALUES (?, ?, ?)");
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

        $novo_dado = [
            'titulo' => $_SESSION['ultimo_nome'], 
            'data' => date('d/m/Y H:i'), 
            'calendar_id' => $calendarIdParaUso,
            'params' => $_SESSION['temp_params']
        ];

        if (isset($_SESSION['editando_id'])) {
            $_SESSION['meus_cronogramas'][$_SESSION['editando_id']] = $novo_dado;
            $idx_final = $_SESSION['editando_id'];
            unset($_SESSION['editando_id']);
        } else {
            $_SESSION['meus_cronogramas'][] = $novo_dado;
            $idx_final = count($_SESSION['meus_cronogramas']) - 1;
        }

        unset($_SESSION['temp_cronograma'], $_SESSION['temp_params']);
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

<main class="main-layout">
    
    <section class="container-principal">
        <?php if ($msg_feedback): ?>
            <div class="alert-message"><?= $msg_feedback ?></div>
        <?php endif; ?>

        <?php if ($calendar_id_exibir && !isset($_GET['editar'])): ?>
            <div class="agenda-header">
                <h3><i class="fa-solid fa-calendar-days"></i> Agenda: <?= htmlspecialchars($_SESSION['meus_cronogramas'][$id_visualizacao]['titulo']) ?></h3>
                <a href="cronograma.php" class="btn-novo-crono">+ Novo Cronograma</a>
            </div>
            <iframe src="https://calendar.google.com/calendar/embed?src=<?= urlencode($calendar_id_exibir) ?>&ctz=America%2FSao_Paulo" class="calendar-embed"></iframe>
        <?php else: ?>
            
            <div class="card-header">
                <div class="title-left">
                    <span class="icon-purple-box">
                        <i class="fa-regular fa-calendar-plus"></i>
                    </span>
                    <div>
                        <h2>Phronesis</h2>
                        <p>Crie e organize seu cronograma de estudos</p>
                    </div>
                </div>
                <a href="?logout=1" class="btn-desconectar">
                    Desconectar <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>

            <form method="POST" class="form-grid">
                <div class="full-width field-group">
                    <label>Nome do seu Cronograma:</label>
                    <input type="text" name="nome_cronograma" placeholder="Ex: Maratona ENEM 2026" 
                           value="<?= htmlspecialchars($dados_edicao['nome_cronograma'] ?? '') ?>" required>
                </div>
                
                <div class="field-group input-icon-wrapper">
                    <label>Data Início:</label>
                    <div class="input-with-icon">
                        <input type="date" name="data_inicio" min="<?= $hoje ?>" value="<?= $dados_edicao['data_inicio'] ?? $hoje ?>" required>
                        <i class="fa-regular fa-calendar input-icon-right"></i>
                    </div>
                </div>
                
                <div class="field-group input-icon-wrapper">
                    <label>Data Fim:</label>
                    <div class="input-with-icon">
                        <input type="date" name="data_fim" min="<?= $hoje ?>" value="<?= $dados_edicao['data_fim'] ?? '' ?>" required>
                        <i class="fa-regular fa-calendar input-icon-right"></i>
                    </div>
                </div>
                
                <div class="full-width field-group">
                    <label>Dias da Semana:</label>
                    <div class="list-container scrolling-list">
                        <?php 
                        $dias = ['Segunda'=>1,'Terça'=>2,'Quarta'=>3,'Quinta'=>4,'Sexta'=>5,'Sábado'=>6,'Domingo'=>7];
                        $selecionados = $dados_edicao['dias_semana'] ?? [1,2,3,4,5,6,7];
                        foreach($dias as $n=>$v): ?>
                            <label class="row-item">
                                <span><?=$n?></span>
                                <input type="checkbox" name="dias_semana[]" value="<?=$v?>" 
                                       <?= in_array($v, $selecionados) ? 'checked' : '' ?> class="custom-checkbox">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="field-group input-icon-wrapper">
                    <label>Hora Início:</label>
                    <div class="input-with-icon">
                        <input type="time" name="hora_inicio" value="<?= $dados_edicao['hora_inicio'] ?? '08:00' ?>" required>
                        <i class="fa-regular fa-clock input-icon-right"></i>
                    </div>
                </div>
                
                <div class="field-group">
                    <label>Duração (horas):</label>
                    <input type="number" name="horas_estudo" value="<?= $dados_edicao['horas_estudo'] ?? '2' ?>" required>
                </div>
                
                <div class="full-width field-group">
                    <label>Matérias Disponíveis:</label>
                    <div class="list-container scrolling-list">
                        <?php 
                        $mats_ativas = $dados_edicao['materias_id'] ?? [];
                        if(empty($lista_materias_db)): ?>
                            <div class="row-item empty-state-select">Selecione as matérias que deseja estudar...</div>
                        <?php else:
                            foreach($lista_materias_db as $m): ?>
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
        
        <?php if(empty($_SESSION['meus_cronogramas'])): ?>
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
                <?php foreach ($_SESSION['meus_cronogramas'] as $id => $cron): ?>
                    <div class="cronograma-item">
                        <a href="?ver=<?= $id ?>" class="crono-link">
                            <span class="crono-title"><?= htmlspecialchars($cron['titulo']) ?></span>
                            <span class="crono-date"><?= $cron['data'] ?></span>
                        </a>
                        <div class="cronograma-actions">
                            <a href="?editar=<?= $id ?>" class="btn-action btn-edit" title="Editar"><i class="fa-solid fa-pen"></i></a>
                            <a href="?deletar_salvo=<?= $id ?>" class="btn-action btn-del" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir do banco e da agenda?')"><i class="fa-solid fa-xmark"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>
</main>

<script>
    (function () {
        const savedTheme = localStorage.getItem('site-theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-theme');
        }
    })();
</script>

<?php include '../perfil/vlibras.php'; ?>

</body>
</html>