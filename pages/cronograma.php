<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

// 1. Lógica de Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['upload_token']);
    header('Location: meu_calendario.php');
    exit;
}

// 2. Proteção de Acesso
if (!isset($_SESSION['upload_token'])) {
    header('Location: meu_calendario.php');
    exit;
}

// 3. Preparação do Cliente Google
$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->setAccessToken($_SESSION['upload_token']);
$service = new Google\Service\Calendar($client);

$cronograma_gerado = null;
$msg_feedback = "";

// Função para traduzir o dia da semana para Português
function traduzirDia($diaIngles) {
    $dias = [
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return $dias[$diaIngles] ?? $diaIngles;
}

// 4. Lógica: Gerar Cronograma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $horas_duracao = $_POST['horas_estudo']; 
    $hora_comeco = $_POST['hora_inicio'];   
    $materias = array_map('trim', explode(',', $_POST['materias']));
    $dias_permitidos = $_POST['dias_semana'] ?? [];

    if ($data_fim < $data_inicio) {
        $msg_feedback = "❌ Erro: A data final deve ser posterior à data de início.";
    } else {
        $intervalo = $data_inicio->diff($data_fim);
        $total_dias = $intervalo->days;

        $cronograma_gerado = [];
        $data_atual = clone $data_inicio;
        $materia_index = 0;

        for ($i = 0; $i <= $total_dias; $i++) {
            $dia_semana_numero = $data_atual->format('N'); 
            
            if (in_array($dia_semana_numero, $dias_permitidos)) {
                $materia_da_vez = $materias[$materia_index % count($materias)];
                
                $inicio_sessao = new DateTime($data_atual->format('Y-m-d') . ' ' . $hora_comeco);
                $fim_sessao = clone $inicio_sessao;
                $fim_sessao->modify('+' . $horas_duracao . ' hours');

                $cronograma_gerado[] = [
                    'data_formatada' => $data_atual->format('d/m/Y'),
                    'dia_semana' => traduzirDia($data_atual->format('l')),
                    'materia' => $materia_da_vez,
                    'horas' => $horas_duracao,
                    'inicio_iso' => $inicio_sessao->format('Y-m-d\TH:i:s'),
                    'fim_iso' => $fim_sessao->format('Y-m-d\TH:i:s')
                ];
                $materia_index++;
            }
            $data_atual->modify('+1 day');
        }
        $_SESSION['temp_cronograma'] = $cronograma_gerado;
    }
}

// 5. Lógica: Sincronizar com Google Agenda
if (isset($_POST['sincronizar']) && isset($_SESSION['temp_cronograma'])) {
    try {
        foreach ($_SESSION['temp_cronograma'] as $evento) {
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Estudo: ' . $evento['materia'],
                'description' => 'Sessão de ' . $evento['horas'] . 'h planejada pelo Phronesis',
                'start' => ['dateTime' => $evento['inicio_iso'], 'timeZone' => 'America/Sao_Paulo'],
                'end'   => ['dateTime' => $evento['fim_iso'], 'timeZone' => 'America/Sao_Paulo'],
            ]);
            $service->events->insert('primary', $event);
        }
        $msg_feedback = "✅ Cronograma sincronizado com sucesso!";
        unset($_SESSION['temp_cronograma']);
    } catch (Exception $e) {
        $msg_feedback = "❌ Erro ao sincronizar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cronograma - Phronesis</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 850px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; margin-bottom: 25px; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        label { font-weight: bold; font-size: 14px; }
        input, button { padding: 12px; border-radius: 8px; border: 1px solid #ddd; width: 100%; box-sizing: border-box; }
        .days-box { background: #f9f9f9; padding: 15px; border-radius: 8px; display: flex; flex-wrap: wrap; gap: 15px; }
        .btn-gen { background: #4285F4; color: white; border: none; font-weight: bold; cursor: pointer; font-size: 16px; }
        .btn-sync { background: #34a853; color: white; border: none; font-weight: bold; cursor: pointer; padding: 15px; width: 100%; font-size: 18px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; }
        .logout-link { color: #d93025; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Phronesis</h1>
        <a href="?logout=1" class="logout-link">Desconectar Agenda</a>
    </div>

    <?php if ($msg_feedback): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #34a853;">
            <?= $msg_feedback ?>
        </div>
    <?php endif; ?>

    <?php if (!$cronograma_gerado): ?>
    <form method="POST" class="form-grid">
        <div>
            <label>Data de Início:</label>
            <input type="date" name="data_inicio" required>
        </div>
        <div>
            <label>Data Final:</label>
            <input type="date" name="data_fim" required>
        </div>

        <div class="full-width">
            <label>Dias da semana para estudar:</label>
            <div class="days-box">
                <label><input type="checkbox" name="dias_semana[]" value="1" checked> Seg</label>
                <label><input type="checkbox" name="dias_semana[]" value="2" checked> Ter</label>
                <label><input type="checkbox" name="dias_semana[]" value="3" checked> Qua</label>
                <label><input type="checkbox" name="dias_semana[]" value="4" checked> Qui</label>
                <label><input type="checkbox" name="dias_semana[]" value="5" checked> Sex</label>
                <label><input type="checkbox" name="dias_semana[]" value="6"> Sáb</label>
                <label><input type="checkbox" name="dias_semana[]" value="7"> Dom</label>
            </div>
        </div>

        <div>
            <label>Horário de início:</label>
            <input type="time" name="hora_inicio" value="09:00" required>
        </div>
        <div>
            <label>Duração da sessão (horas):</label>
            <input type="number" name="horas_estudo" value="2" min="1" required>
        </div>

        <div class="full-width">
            <label>Matérias (separadas por vírgula):</label>
            <input type="text" name="materias" placeholder="Ex: Matemática, Português, Hardware" required>
        </div>

        <button type="submit" name="gerar" class="btn-gen full-width">Gerar Plano de Estudos</button>
    </form>
    <?php endif; ?>

    <?php if ($cronograma_gerado): ?>
        <h3>Confirme seu Cronograma:</h3>
        <table>
            <thead>
                <tr>
                    <th>Data (Dia)</th>
                    <th>Matéria</th>
                    <th>Horário</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cronograma_gerado as $item): ?>
                <tr>
                    <td><strong><?= $item['data_formatada'] ?></strong> <br><small style="color: #666;"><?= $item['dia_semana'] ?></small></td>
                    <td><?= $item['materia'] ?></td>
                    <td><?= date('H:i', strtotime($item['inicio_iso'])) ?> - <?= date('H:i', strtotime($item['fim_iso'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST">
            <button type="submit" name="sincronizar" class="btn-sync">Confirmar e Enviar para Google Agenda</button>
        </form>
        <p align="center" style="margin-top: 20px;"><a href="cronograma.php" style="color: #4285F4; text-decoration: none;">← Voltar e Ajustar Configurações</a></p>
    <?php endif; ?>
</div>

</body>
</html>