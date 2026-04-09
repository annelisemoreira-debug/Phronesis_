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

// 4. Lógica: Gerar Cronograma (Quando clica no botão azul)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $horas = $_POST['horas_estudo'];
    $materias = array_map('trim', explode(',', $_POST['materias']));

    $intervalo = $data_inicio->diff($data_fim);
    $total_dias = $intervalo->days;

    $cronograma_gerado = [];
    $data_atual = clone $data_inicio;

    for ($i = 0; $i <= $total_dias; $i++) {
        $materia_da_vez = $materias[$i % count($materias)];
        $cronograma_gerado[] = [
            'data' => $data_atual->format('d/m/Y'),
            'data_api' => $data_atual->format('Y-m-d'),
            'dia_nome' => $data_atual->format('l'),
            'materia' => $materia_da_vez,
            'horas' => $horas
        ];
        $data_atual->modify('+1 day');
    }
    $_SESSION['temp_cronograma'] = $cronograma_gerado;
}

// 5. Lógica: Sincronizar com Google (Quando clica no botão verde)
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
        input, button { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        .full-width { grid-column: span 2; }
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
            <label>Horas livres por dia:</label><br>
            <input type="number" name="horas_estudo" placeholder="Ex: 4" required style="width: 100%;">
        </div>
        <div class="full-width">
            <label>Matérias de necessidade (separadas por vírgula):</label><br>
            <input type="text" name="materias" placeholder="Física, Matemática, Português" required style="width: 100%;">
        </div>
        <button type="submit" name="gerar" class="btn-primary full-width">Gerar Cronograma</button>
    </form>
    <?php endif; ?>

    <?php if ($cronograma_gerado || (isset($_SESSION['temp_cronograma']) && isset($_POST['gerar']))): ?>
        <?php $exibir = $cronograma_gerado ?? $_SESSION['temp_cronograma']; ?>
        <h3>Seu Plano de Estudos:</h3>
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