<?php
// Conexão com o banco (ajuste suas credenciais)
$pdo = new PDO("mysql:host=localhost;dbname=phronesis;charset=utf8mb4", "root", "");

// Pegando dados do formulário
$dias_escolhidos = $_POST['dias'] ?? [];
$horas_disponiveis = $_POST['horas_total'] ?? 0;
$id_usuario = 1; // Em um sistema real, viria da SESSION

if (empty($dias_escolhidos)) {
    die("Por favor, selecione ao menos um dia da semana. <a href='index.php'>Voltar</a>");
}

// 1. Buscar matérias do usuário
$sql = "SELECT m.nome_materia, m.dificuldade, l.nome_livro 
        FROM materia m 
        LEFT JOIN livro l ON m.id_materia = l.id_materia 
        WHERE m.id_usuarioc = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Cálculo do Peso Total
$soma_pesos = array_sum(array_column($materias, 'dificuldade'));

if ($soma_pesos == 0) {
    die("Cadastre matérias com dificuldade antes de gerar o cronograma.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Seu Cronograma Personalizado</title>
    <style>
        body { font-family: sans-serif; background: #eef2f3; padding: 20px; color: #333; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .dia-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .dia-titulo { border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 15px; color: #2c3e50; }
        .materia-linha { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .badge-tempo { background: #27ae60; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

<h1>🎯 Seu Plano de Estudos</h1>

<div class="grid">
    <?php foreach ($dias_escolhidos as $dia): ?>
        <div class="dia-card">
            <h2 class="dia-titulo"><?php echo $dia; ?></h2>
            
            <?php foreach ($materias as $m): 
                // Lógica de cálculo proporcional
                $minutos_totais = ($m['dificuldade'] / $soma_pesos) * ($horas_disponiveis * 60);
                $h = floor($minutos_totais / 60);
                $min = round($minutos_totais % 60);
            ?>
                <div class="materia-linha">
                    <div>
                        <strong><?php echo $m['nome_materia']; ?></strong><br>
                        <small style="color: #7f8c8d;"><?php echo $m['nome_livro'] ?? 'Material Geral'; ?></small>
                    </div>
                    <span class="badge-tempo"><?php echo "{$h}h {$min}min"; ?></span>
                </div>
            <?php endforeach; ?>
            
        </div>
    <?php endforeach; ?>
</div>

<p><a href="index.php">⬅️ Refazer cronograma</a></p>

</body>
</html>