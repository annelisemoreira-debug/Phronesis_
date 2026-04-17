<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

// --- DEFINIÇÃO DO FUSO HORÁRIO PADRÃO DO PHP ---
date_default_timezone_set('America/Sao_Paulo');
$hoje = date('Y-m-d'); 

// --- 1. CONEXÃO COM O BANCO DE DADOS ---
// --- SENHA PARA CONEXÃO: Home@spSENAI2025! ---

try {
    $pdo = new PDO("mysql:host=localhost;dbname=phronesis;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Erro banco: " . $e->getMessage()); 
}

// --- 2. BUSCA DE MATÉRIAS ---
$sql = "SELECT id_materia, nome_materia FROM materia ORDER BY nome_materia ASC";
$consulta = $pdo->query($sql);
$lista_materias_db = $consulta->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['meus_cronogramas'])) {
    $_SESSION['meus_cronogramas'] = [];
}

// --- 3. LOGICA DE ACESSO E GOOGLE CLIENT ---
if (isset($_GET['logout'])) {
    unset($_SESSION['upload_token'], $_SESSION['form_data'], $_SESSION['temp_cronograma'], $_SESSION['temp_params'], $_SESSION['editando_id']);
    header('Location: meu_calendario.php');
    exit;
}

if (!isset($_SESSION['upload_token'])) {
    header('Location: meu_calendario.php');
    exit;
}

$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->setAccessToken($_SESSION['upload_token']);
$service = new Google\Service\Calendar($client);

$msg_feedback = "";
$id_visualizacao = $_GET['ver'] ?? null;
$mostrar_modal = false;

// --- 4. EXCLUSÃO ---
if (isset($_GET['deletar_salvo'])) {
    $id = $_GET['deletar_salvo'];
    if (isset($_SESSION['meus_cronogramas'][$id])) {
        $calendarId = $_SESSION['meus_cronogramas'][$id]['calendar_id'] ?? null;
        try {
            if ($calendarId && $calendarId !== 'primary') $service->calendars->delete($calendarId);
            $msg_feedback = "🗑️ Cronograma removido!";
        } catch (Exception $e) { $msg_feedback = "⚠️ Erro ao remover agenda."; }
        unset($_SESSION['meus_cronogramas'][$id]);
    }
    header('Location: cronograma.php?msg=' . urlencode($msg_feedback));
    exit;
}

// --- 4.5. LÓGICA DE RECUPERAÇÃO DE DADOS PARA EDIÇÃO ---
$dados_edicao = null;
if (isset($_GET['editar'])) {
    $id_ed = $_GET['editar'];
    if (isset($_SESSION['meus_cronogramas'][$id_ed])) {
        $dados_edicao = $_SESSION['meus_cronogramas'][$id_ed]['params'] ?? null;
        $_SESSION['editando_id'] = $id_ed; // Salva quem estamos editando
        $id_visualizacao = null; 
    }
}

if (isset($_GET['msg'])) $msg_feedback = $_GET['msg'];

// --- 5. LÓGICA: GERAR CRONOGRAMA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $materias_selecionadas = $_POST['materias_id'] ?? [];
    $dias_permitidos = $_POST['dias_semana'] ?? [];

    if (empty($materias_selecionadas)) {
        $msg_feedback = "❌ Erro: Selecione ao menos uma matéria.";
    } elseif ($data_fim < $data_inicio) {
        $msg_feedback = "❌ Erro: Data final inválida.";
    } else {
        $_SESSION['ultimo_nome'] = !empty($_POST['nome_cronograma']) ? $_POST['nome_cronograma'] : ("Plano de Estudos - " . date('d/m'));

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

// --- 6. LÓGICA: SINCRONIZAR ---
if (isset($_POST['sincronizar']) && isset($_SESSION['temp_cronograma'])) {
    try {
        $pdo->beginTransaction(); 
        $id_usuario_logado = $_SESSION['id_usuario'] ?? 1;

        // Se for edição, removemos a agenda antiga do Google antes de criar a nova (opcional, mas evita lixo no Google)
        if (isset($_SESSION['editando_id'])) {
            $old_id = $_SESSION['editando_id'];
            $old_calendar_id = $_SESSION['meus_cronogramas'][$old_id]['calendar_id'] ?? null;
            if ($old_calendar_id && $old_calendar_id !== 'primary') {
                try { $service->calendars->delete($old_calendar_id); } catch (Exception $e) {}
            }
        }

        $newCalendar = new Google\Service\Calendar\Calendar();
        $newCalendar->setSummary('Phronesis: ' . $_SESSION['ultimo_nome']);
        $newCalendar->setTimeZone('America/Sao_Paulo');
        
        $createdCalendar = $service->calendars->insert($newCalendar);
        $newCalendarId = $createdCalendar->getId();

        $stmtCrono = $pdo->prepare("INSERT INTO cronograma (id_usuario, titulo_cronograma, google_calendar_id) VALUES (?, ?, ?)");
        $stmtCrono->execute([$id_usuario_logado, $_SESSION['ultimo_nome'], $newCalendarId]);
        $id_novo_cronograma = $pdo->lastInsertId();

        foreach ($_SESSION['temp_cronograma'] as $evento) {
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Estudo: ' . $evento['materia'],
                'start' => ['dateTime' => $evento['inicio_iso'], 'timeZone' => 'America/Sao_Paulo'],
                'end'   => ['dateTime' => $evento['fim_iso'], 'timeZone' => 'America/Sao_Paulo'],
            ]);
            $google_event = $service->events->insert($newCalendarId, $event);

            $stmtItem = $pdo->prepare("INSERT INTO cronograma_item (id_cronograma, id_materia, google_event_id, data_estudo, data_fim) VALUES (?, ?, ?, ?, ?)");
            $stmtItem->execute([$id_novo_cronograma, $evento['id_materia'], $google_event->getId(), str_replace('T', ' ', $evento['inicio_iso']), str_replace('T', ' ', $evento['fim_iso'])]);
        }

        $pdo->commit();

        $novo_dado = [
            'titulo' => $_SESSION['ultimo_nome'], 
            'data' => date('d/m/Y H:i'), 
            'calendar_id' => $newCalendarId,
            'params' => $_SESSION['temp_params']
        ];

        // CORREÇÃO: Verifica se deve substituir ou adicionar
        if (isset($_SESSION['editando_id'])) {
            $idx = $_SESSION['editando_id'];
            $_SESSION['meus_cronogramas'][$idx] = $novo_dado;
            unset($_SESSION['editando_id']);
        } else {
            $_SESSION['meus_cronogramas'][] = $novo_dado;
            $idx = count($_SESSION['meus_cronogramas']) - 1;
        }

        unset($_SESSION['temp_cronograma'], $_SESSION['temp_params']);
        header('Location: cronograma.php?ver=' . $idx);
        exit;
    } catch (Exception $e) { 
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg_feedback = "❌ Erro ao sincronizar: " . $e->getMessage(); 
    }
}

$calendar_id_exibir = null;
if ($id_visualizacao !== null && isset($_SESSION['meus_cronogramas'][$id_visualizacao])) {
    $calendar_id_exibir = $_SESSION['meus_cronogramas'][$id_visualizacao]['calendar_id'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Phronesis - Cronograma</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; color: #333; }
        .main-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; max-width: 1200px; margin: auto; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .sidebar { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 25px; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input, button { padding: 12px; border-radius: 8px; border: 1px solid #ddd; width: 100%; box-sizing: border-box; }
        .list-container { border: 1px solid #ddd; border-radius: 8px; max-height: 200px; overflow-y: auto; background: #fff; margin-top: 5px; }
        .row-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f0f0f0; }
        .row-item:last-child { border-bottom: none; }
        .row-item input[type="checkbox"] { width: 18px; height: 18px; margin: 0; cursor: pointer; }
        .btn-gen { background: #4285F4; color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-sync { background: #34a853; color: white; border: none; font-weight: bold; cursor: pointer; padding: 15px; }
        .cronograma-item { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 10px; position: relative; border-left: 4px solid #4285F4; }
        .cronograma-actions { position: absolute; right: 10px; top: 12px; display: flex; gap: 10px; }
        .btn-action { text-decoration: none; font-weight: bold; font-size: 16px; }
        .btn-del { color: #ea4335; }
        .btn-edit { color: #4285F4; }
        .calendar-embed { width: 100%; height: 600px; border: none; margin-top: 20px; border-radius: 8px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; width: 450px; border-radius: 15px; padding: 25px; text-align: center; }
    </style>
</head>
<body>

<div id="confirmModal" class="modal" style="<?= $mostrar_modal ? 'display:block;' : '' ?>">
    <div class="modal-content">
        <h3>📚 Confirmar Cronograma</h3>
        <p>Título: <b><?= htmlspecialchars($_SESSION['ultimo_nome'] ?? '') ?></b></p>
        <form method="POST">
            <button type="submit" name="sincronizar" class="btn-sync">Confirmar e Salvar</button>
            <button type="button" onclick="window.location.href='cronograma.php'" style="margin-top:10px; background:#f1f1f1; border:none; cursor:pointer; padding:10px; border-radius:8px; width:100%;">Cancelar</button>
        </form>
    </div>
</div>

<div class="main-layout">
    <div class="container">
        <div class="header">
            <h1>Phronesis</h1>
            <a href="?logout=1" style="color:#d93025; text-decoration:none; font-weight:bold; font-size:14px;">Desconectar</a>
        </div>

        <?php if ($msg_feedback): ?>
            <div style="background:#e8f0fe; color:#1967d2; padding:15px; border-radius:8px; margin-bottom:20px; border: 1px solid #d2e3fc;"><?= $msg_feedback ?></div>
        <?php endif; ?>

        <?php if ($calendar_id_exibir && !isset($_GET['editar'])): ?>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3>📅 Agenda: <?= htmlspecialchars($_SESSION['meus_cronogramas'][$id_visualizacao]['titulo']) ?></h3>
                <a href="cronograma.php" style="color:#4285F4; text-decoration:none; font-weight:bold;">+ Novo Cronograma</a>
            </div>
            <iframe src="https://calendar.google.com/calendar/embed?src=<?= urlencode($calendar_id_exibir) ?>&ctz=America%2FSao_Paulo" class="calendar-embed"></iframe>
        <?php else: ?>
            <form method="POST" class="form-grid">
                <div class="full-width">
                    <label>Nome do seu Cronograma:</label>
                    <input type="text" name="nome_cronograma" placeholder="Ex: Maratona ENEM 2026" 
                           value="<?= htmlspecialchars($dados_edicao['nome_cronograma'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Data Início:</label>
                    <input type="date" name="data_inicio" min="<?= $hoje ?>" value="<?= $dados_edicao['data_inicio'] ?? $hoje ?>" required>
                </div>
                <div>
                    <label>Data Fim:</label>
                    <input type="date" name="data_fim" min="<?= $hoje ?>" value="<?= $dados_edicao['data_fim'] ?? '' ?>" required>
                </div>
                <div class="full-width">
                    <label>Dias da Semana:</label>
                    <div class="list-container">
                        <?php 
                        $dias = ['Segunda'=>1,'Terça'=>2,'Quarta'=>3,'Quinta'=>4,'Sexta'=>5,'Sábado'=>6,'Domingo'=>7];
                        $selecionados = $dados_edicao['dias_semana'] ?? [1,2,3,4,5,6,7];
                        foreach($dias as $n=>$v): ?>
                            <label class="row-item">
                                <span><?=$n?></span>
                                <input type="checkbox" name="dias_semana[]" value="<?=$v?>" 
                                       <?= in_array($v, $selecionados) ? 'checked' : '' ?>>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <label>Hora Início:</label>
                    <input type="time" name="hora_inicio" value="<?= $dados_edicao['hora_inicio'] ?? '08:00' ?>" required>
                </div>
                <div>
                    <label>Duração (horas):</label>
                    <input type="number" name="horas_estudo" value="<?= $dados_edicao['horas_estudo'] ?? '2' ?>" required>
                </div>
                <div class="full-width">
                    <label>Matérias Disponíveis:</label>
                    <div class="list-container">
                        <?php 
                        $mats_ativas = $dados_edicao['materias_id'] ?? [];
                        foreach($lista_materias_db as $m): ?>
                            <label class="row-item">
                                <span><?= htmlspecialchars($m['nome_materia']) ?></span>
                                <input type="checkbox" name="materias_id[]" value="<?= $m['id_materia'] ?>"
                                       <?= in_array($m['id_materia'], $mats_ativas) ? 'checked' : '' ?>>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" name="gerar" class="btn-gen full-width">
                    <?= isset($_GET['editar']) ? 'Regerar e Salvar Alterações' : 'Gerar e Visualizar' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <h3>Histórico</h3>
        <?php foreach ($_SESSION['meus_cronogramas'] as $id => $cron): ?>
            <div class="cronograma-item">
                <a href="?ver=<?= $id ?>" style="text-decoration:none; color:inherit; display:block; padding-right:45px;">
                    <b><?= htmlspecialchars($cron['titulo']) ?></b><br>
                    <small><?= $cron['data'] ?></small>
                </a>
                <div class="cronograma-actions">
                    <a href="?editar=<?= $id ?>" class="btn-action btn-edit" title="Editar">📝</a>
                    <a href="?deletar_salvo=<?= $id ?>" class="btn-action btn-del" onclick="return confirm('Excluir?')">×</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>