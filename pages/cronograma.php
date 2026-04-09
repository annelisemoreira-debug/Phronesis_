<?php
session_start();
require_once '../vendor/autoload.php';

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

// 4. Lógica: Gerar Cronograma (Modificada para filtrar dias)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $horas = $_POST['horas_estudo'];
    $materias = array_map('trim', explode(',', $_POST['materias']));
    $dias_permitidos = $_POST['dias_semana'] ?? []; // Pega os dias marcados

    $intervalo = $data_inicio->diff($data_fim);
    $total_dias = $intervalo->days;

    $cronograma_gerado = [];
    $data_atual = clone $data_inicio;
    $materia_index = 0;

    for ($i = 0; $i <= $total_dias; $i++) {
        // 'N' retorna 1 (segunda) até 7 (domingo)
        $dia_semana_numero = $data_atual->format('N'); 
        
        // Verifica se o dia atual foi selecionado no formulário
        if (in_array($dia_semana_numero, $dias_permitidos)) {
            $materia_da_vez = $materias[$materia_index % count($materias)];
            
            $cronograma_gerado[] = [
                'data' => $data_atual->format('d/m/Y'),
                'data_api' => $data_atual->format('Y-m-d'),
                'dia_nome' => $data_atual->format('l'),
                'materia' => $materia_da_vez,
                'horas' => $horas
            ];
            $materia_index++; // Só pula para a próxima matéria se houve estudo no dia
        }
        $data_atual->modify('+1 day');
    }
    $_SESSION['temp_cronograma'] = $cronograma_gerado;
}

// 5. Lógica: Sincronizar com Google (Mantida igual)
if (isset($_POST['sincronizar']) && isset($_SESSION['temp_cronograma'])) {
    try {
        foreach ($_SESSION['temp_cronograma'] as $evento) {
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Estudo: ' . $evento['materia'],
                'description' => 'Planejado via Phronesis (' . $evento['horas'] . 'h de duração)',
                'start' => ['dateTime' => $evento['data_api'] . 'T09:00:00', 'timeZone' => 'America/Sao_Paulo'],
                'end'   => ['dateTime' => $evento['data_api'] . 'T12:00:00', 'timeZone' => 'America/Sao_Paulo'],
            ]);
            $service->events->insert('primary', $event);
        }
        $msg_feedback = "✅ Sucesso! O cronograma foi enviado para sua Agenda!";
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
    <title>Meu Cronograma - Phronesis</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        input[type="text"], input[type="date"], input[type="number"], button { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        .full-width { grid-column: span 2; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; background: #f1f1f1; padding: 15px; border-radius: 8px; }
        .checkbox-item { display: flex; align-items: center; gap: 5px; font-size: 14px; }
        .btn-primary { background: #4285F4; color: white; border: none; cursor: pointer; font-weight: bold; }
        .btn-success { background: #34a853; color: white; border: none; cursor: pointer; font-weight: bold; width: 100%; margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .logout { color: #d93025; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Phronesis - Cronograma</h1>
        <a href="?logout=1" class="logout">Sair</a>
    </div>

    <?php if ($msg_feedback): ?>
        <div style="padding: 15px; background: #e7f3ef; border-radius: 8px; margin-bottom: 20px;"><?= $msg_feedback ?></div>
    <?php endif; ?>

    <?php if (!$cronograma_gerado && !isset($_POST['sincronizar'])): ?>
    <form method="POST" class="form-grid">
        <div>
            <label>Data de Início:</label><br>
            <input type="date" name="data_inicio" required style="width: 100%;">
        </div>
        <div>
            <label>Data Final:</label><br>
            <input type="date" name="data_fim" required style="width: 100%;">
        </div>
        
        <div class="full-width">
            <label><strong>Dias da semana que deseja estudar:</strong></label>
            <div class="checkbox-group">
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="1" checked> Seg</div>
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="2" checked> Ter</div>
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="3" checked> Qua</div>
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="4" checked> Qui</div>
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="5" checked> Sex</div>
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="6"> Sáb</div>
                <div class="checkbox-item"><input type="checkbox" name="dias_semana[]" value="7"> Dom</div>
            </div>
        </div>

        <div class="full-width">
            <label>Horas livres por dia:</label><br>
            <input type="number" name="horas_estudo" placeholder="Ex: 4" required style="width: 100%;">
        </div>
        <div class="full-width">
            <label>Matérias de necessidade (separadas por vírgula):</label><br>
            <input type="text" name="materias" placeholder="Física, Matemática, Português" required style="width: 100%;">
        </div>
        <button type="submit" name="gerar" class="btn-primary full-width">Gerar Cronograma Personalizado</button>
    </form>
    <?php endif; ?>

    <?php if ($cronograma_gerado || (isset($_SESSION['temp_cronograma']) && isset($_POST['gerar']))): ?>
        <?php $exibir = $cronograma_gerado ?? $_SESSION['temp_cronograma']; ?>
        <h3>Seu Plano de Estudos Personalizado:</h3>
        <table class="table">
            <thead>
                <tr><th>Data</th><th>Matéria</th><th>Tempo</th></tr>
            </thead>
            <tbody>
                <?php foreach ($exibir as $linha): ?>
                <tr>
                    <td><?= $linha['data'] ?> (<?= $linha['dia_nome'] ?>)</td>
                    <td><?= $linha['materia'] ?></td>
                    <td><?= $linha['horas'] ?>h</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <form method="POST">
            <button type="submit" name="sincronizar" class="btn-success">Confirmar e Enviar para o Google Agenda</button>
        </form>
        <p align="center"><a href="cronograma.php">Refazer do Zero</a></p>
    <?php endif; ?>
</div>

</body>
</html>