<?php
require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/../credentials.json');
$client->addScope(Google\Service\Calendar::CALENDAR);
$tokenPath = __DIR__ . '/../token.json';

if (!file_exists($tokenPath)) {
    die("Erro: token.json não encontrado. Vá ao index.php primeiro.");
}

$client->setAccessToken(json_decode(file_get_contents($tokenPath), true));
$service = new Google\Service\Calendar($client);

// --- LÓGICA DE PROCESSAMENTO (Só acontece após clicar no botão) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data_inicio'])) {
    
    $dataInicio = new DateTime($_POST['data_inicio']);
    $dataFim = new DateTime($_POST['data_fim']);
    $horasLivres = $_POST['horas_livres'];
    $materias = explode(',', $_POST['materias']);

    $intervalo = $dataInicio->diff($dataFim);
    $totalDias = $intervalo->days;

    $currentDate = clone $dataInicio;
    $materiaIndex = 0;

    for ($i = 0; $i <= $totalDias; $i++) {
        $materiaDoDia = trim($materias[$materiaIndex]);

        $event = new Google\Service\Calendar\Event([
            'summary' => "Estudar: $materiaDoDia",
            'description' => "Focar por $horasLivres horas - Gerado por Phronesis",
            'start' => [
                'dateTime' => $currentDate->format('Y-m-d') . 'T09:00:00',
                'timeZone' => 'America/Sao_Paulo',
            ],
            'end' => [
                'dateTime' => $currentDate->format('Y-m-d') . 'T' . (9 + (int)$horasLivres) . ':00:00',
                'timeZone' => 'America/Sao_Paulo',
            ],
        ]);

        $service->events->insert('primary', $event);
        $materiaIndex = ($materiaIndex + 1) % count($materias);
        $currentDate->modify('+1 day');
    }

    echo "<h2>✅ Cronograma de $totalDias dias criado com sucesso!</h2>";
    echo "<a href='https://calendar.google.com' target='_blank'>Abrir meu Google Agenda</a><br><br>";
    echo "<a href='cronograma.php'>Criar outro plano</a>";
    exit; // Para a execução aqui para não mostrar o formulário de novo
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Phronesis - Planejamento</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
        input, textarea { margin-bottom: 10px; width: 300px; display: block; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🎯 Monte seu Cronograma de Estudos</h1>
    <form action="cronograma.php" method="POST">
        <label>Data de Início:</label>
        <input type="date" name="data_inicio" required>

        <label>Data Final (Meta):</label>
        <input type="date" name="data_fim" required>

        <label>Horas livres por dia:</label>
        <input type="number" name="horas_livres" min="1" max="12" required>

        <label>Matérias (separe por vírgula):</label>
        <textarea name="materias" placeholder="Ex: Matemática, Português, História" required></textarea>

        <button type="submit">Gerar no Google Calendar</button>
    </form>
</body>
</html>