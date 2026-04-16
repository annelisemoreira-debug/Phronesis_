<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['meus_cronogramas'])) {
    $_SESSION['meus_cronogramas'] = [];
}

// 1. Lógica de Logout
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

// --- LÓGICA DE CARREGAR PARA EDIÇÃO ---
$dados_edicao = null;
if (isset($_GET['editar'])) {
    $id_edit = $_GET['editar'];
    if (isset($_SESSION['meus_cronogramas'][$id_edit])) {
        $dados_edicao = $_SESSION['meus_cronogramas'][$id_edit]['params'] ?? null;
        $_SESSION['editando_id'] = $id_edit; // Salva na sessão que estamos editando este ID
    }
}

// LÓGICA DE EXCLUSÃO
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

if (isset($_GET['msg'])) $msg_feedback = $_GET['msg'];

// 4. Lógica: Gerar Cronograma (Temporário)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $materias = array_map('trim', explode(',', $_POST['materias']));
    $dias_permitidos = $_POST['dias_semana'] ?? [];

    if ($data_fim < $data_inicio) {
        $msg_feedback = "❌ Erro: Data final inválida.";
    } else {
        $cronograma_gerado = [];
        $data_atual = clone $data_inicio;
        $materia_index = 0;
        $intervalo = $data_inicio->diff($data_fim);

        for ($i = 0; $i <= $intervalo->days; $i++) {
            if (in_array($data_atual->format('N'), $dias_permitidos)) {
                $inicio_sessao = new DateTime($data_atual->format('Y-m-d') . ' ' . $_POST['hora_inicio']);
                $fim_sessao = clone $inicio_sessao;
                $fim_sessao->modify('+' . $_POST['horas_estudo'] . ' hours');

                $cronograma_gerado[] = [
                    'materia' => $materias[$materia_index % count($materias)],
                    'inicio_iso' => $inicio_sessao->format('Y-m-d\TH:i:s'),
                    'fim_iso' => $fim_sessao->format('Y-m-d\TH:i:s')
                ];
                $materia_index++;
            }
            $data_atual->modify('+1 day');
        }
        $_SESSION['temp_cronograma'] = $cronograma_gerado;
        $_SESSION['ultimo_nome'] = implode(", ", $materias);
        $_SESSION['temp_params'] = $_POST;
        $mostrar_modal = true;
    }
}

// 5. Lógica: Sincronizar (Salvar ou Atualizar)
if (isset($_POST['sincronizar']) && isset($_SESSION['temp_cronograma'])) {
    try {
        // 1. Criar nova agenda para os dados editados/novos
        $newCalendar = new Google\Service\Calendar\Calendar();
        $newCalendar->setSummary('Phronesis: ' . $_SESSION['ultimo_nome']);
        $newCalendar->setTimeZone('America/Sao_Paulo');
        $createdCalendar = $service->calendars->insert($newCalendar);
        $newCalendarId = $createdCalendar->getId();

        // Permissão pública de leitura (para o iframe funcionar)
        $rule = new Google\Service\Calendar\AclRule();
        $scope = new Google\Service\Calendar\AclRuleScope();
        $scope->setType("default");
        $rule->setScope($scope);
        $rule->setRole("reader");
        $service->acl->insert($newCalendarId, $rule);

        foreach ($_SESSION['temp_cronograma'] as $evento) {
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Estudo: ' . $evento['materia'],
                'start' => ['dateTime' => $evento['inicio_iso'], 'timeZone' => 'America/Sao_Paulo'],
                'end'   => ['dateTime' => $evento['fim_iso'], 'timeZone' => 'America/Sao_Paulo'],
            ]);
            $service->events->insert($newCalendarId, $event);
        }

        $dados_finais = [
            'titulo' => $_SESSION['ultimo_nome'],
            'data' => date('d/m/Y H:i') . (isset($_SESSION['editando_id']) ? " (Editado)" : ""),
            'calendar_id' => $newCalendarId,
            'params' => $_SESSION['temp_params']
        ];

        // SE ESTIVER EDITANDO: Deleta a agenda antiga e substitui na mesma posição
        if (isset($_SESSION['editando_id']) && isset($_SESSION['meus_cronogramas'][$_SESSION['editando_id']])) {
            $id_antigo = $_SESSION['editando_id'];
            $oldCalendarId = $_SESSION['meus_cronogramas'][$id_antigo]['calendar_id'] ?? null;
            if ($oldCalendarId) { try { $service->calendars->delete($oldCalendarId); } catch(Exception $e){} }
            
            $_SESSION['meus_cronogramas'][$id_antigo] = $dados_finais;
            $ver_id = $id_antigo;
        } else {
            $_SESSION['meus_cronogramas'][] = $dados_finais;
            $ver_id = count($_SESSION['meus_cronogramas']) - 1;
        }

        unset($_SESSION['temp_cronograma'], $_SESSION['temp_params'], $_SESSION['editando_id']);
        header('Location: cronograma.php?ver=' . $ver_id);
        exit;
    } catch (Exception $e) { $msg_feedback = "❌ Erro: " . $e->getMessage(); }
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
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .main-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; max-width: 1200px; margin: auto; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .sidebar { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 25px; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        input, button { padding: 12px; border-radius: 8px; border: 1px solid #ddd; width: 100%; box-sizing: border-box; }
        .btn-gen { background: #4285F4; color: white; border: none; font-weight: bold; cursor: pointer; }
        .btn-sync { background: #34a853; color: white; border: none; font-weight: bold; cursor: pointer; padding: 15px; }
        .cronograma-item { background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 10px; position: relative; border-left: 4px solid #4285F4; }
        .cronograma-link { text-decoration: none; color: inherit; display: block; padding-right: 60px; }
        .btn-del { color: #ea4335; text-decoration: none; position: absolute; right: 10px; top: 12px; font-weight: bold; }
        .btn-edit { color: #f4b400; text-decoration: none; position: absolute; right: 35px; top: 10px; font-size: 18px; }
        .calendar-embed { width: 100%; height: 600px; border: none; margin-top: 20px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; width: 450px; border-radius: 15px; padding: 20px; text-align: center; }
    </style>
</head>
<body>

<div id="confirmModal" class="modal" style="<?= $mostrar_modal ? 'display:block;' : '' ?>">
    <div class="modal-content">
        <h3>📚 Confirmar Alterações</h3>
        <p>Deseja aplicar estas mudanças na sua agenda?</p>
        <form method="POST">
            <button type="submit" name="sincronizar" class="btn-sync">Sim, Sincronizar Agora</button>
            <button type="button" onclick="window.location.href='cronograma.php'" style="margin-top:10px; background:#ccc; border:none; cursor:pointer;">Cancelar</button>
        </form>
    </div>
</div>

<div class="main-layout">
    <div class="container">
        <div class="header">
            <h1>Phronesis</h1>
            <a href="?logout=1" style="color:#d93025; text-decoration:none; font-weight:bold;">Desconectar</a>
        </div>

        <?php if ($msg_feedback): ?>
            <div style="background:#e8f5e9; padding:15px; border-radius:8px; margin-bottom:20px;"><?= $msg_feedback ?></div>
        <?php endif; ?>

        <?php if ($calendar_id_exibir): ?>
            <h3>📅 Exibindo: <?= htmlspecialchars($_SESSION['meus_cronogramas'][$id_visualizacao]['titulo']) ?></h3>
            <iframe src="https://calendar.google.com/calendar/embed?src=<?= urlencode($calendar_id_exibir) ?>&ctz=America%2FSao_Paulo" class="calendar-embed"></iframe>
            <a href="cronograma.php" style="display:block; text-align:center; margin-top:15px; color:#4285F4;">+ Criar novo cronograma</a>
        <?php else: ?>
            <form method="POST" class="form-grid">
                <div><label>Início:</label><input type="date" name="data_inicio" value="<?= $dados_edicao['data_inicio'] ?? '' ?>" required></div>
                <div><label>Fim:</label><input type="date" name="data_fim" value="<?= $dados_edicao['data_fim'] ?? '' ?>" required></div>
                <div class="full-width">
                    <label>Dias:</label><br>
                    <?php 
                    $dias = ['Seg'=>1,'Ter'=>2,'Qua'=>3,'Qui'=>4,'Sex'=>5,'Sáb'=>6,'Dom'=>7];
                    $sel = $dados_edicao['dias_semana'] ?? [1,2,3,4,5,6,7];
                    foreach($dias as $n=>$v): ?>
                        <label><input type="checkbox" name="dias_semana[]" value="<?=$v?>" <?= in_array($v, $sel) ? 'checked' : '' ?>> <?=$n?></label>
                    <?php endforeach; ?>
                </div>
                <div><label>Hora:</label><input type="time" name="hora_inicio" value="<?= $dados_edicao['hora_inicio'] ?? '09:00' ?>" required></div>
                <div><label>Duração (h):</label><input type="number" name="horas_estudo" value="<?= $dados_edicao['horas_estudo'] ?? '2' ?>" required></div>
                <div class="full-width">
                    <label>Matérias:</label>
                    <input type="text" name="materias" value="<?= $dados_edicao['materias'] ?? '' ?>" required placeholder="Matemática, Física...">
                </div>
                <button type="submit" name="gerar" class="btn-gen full-width"><?= isset($_GET['editar']) ? '🔄 Atualizar Este Cronograma' : 'Gerar Novo Plano' ?></button>
            </form>
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <h3>Meus Planos</h3>
        <?php foreach ($_SESSION['meus_cronogramas'] as $id => $cron): ?>
            <div class="cronograma-item">
                <a href="?ver=<?= $id ?>" class="cronograma-link">
                    <b><?= htmlspecialchars($cron['titulo']) ?></b><br>
                    <small><?= $cron['data'] ?></small>
                </a>
                <a href="?editar=<?= $id ?>" class="btn-edit" title="Editar">✎</a>
                <a href="?deletar_salvo=<?= $id ?>" class="btn-del" onclick="return confirm('Excluir agenda?')">×</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>