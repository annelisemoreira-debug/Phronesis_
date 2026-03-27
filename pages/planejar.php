<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planejar Estudos - Phronesis</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <style>
        /* Um estilo rápido para não ficar bagunçado */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; padding: 40px; }
        .container { max-width: 500px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: auto; }
        h2 { color: #333; text-align: center; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #555; }
        input, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; margin-top: 25px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background 0.3s; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="container">
    <h2>🎯 Montar Cronograma</h2>
    <p style="text-align: center; color: #777;">Preencha seus horários para integrarmos ao Google Calendar</p>
    
    <form action="cronograma.php" method="POST">
        
        <label for="data_inicio">Data de Início:</label>
        <input type="date" id="data_inicio" name="data_inicio" required>

        <label for="data_fim">Data Final (Meta):</label>
        <input type="date" id="data_fim" name="data_fim" required>

        <label for="horas_livres">Horas livres para estudo por dia:</label>
        <input type="number" id="horas_livres" name="horas_livres" min="1" max="12" placeholder="Ex: 4" required>

        <label for="materias">Matérias de maior necessidade:</label>
        <textarea id="materias" name="materias" rows="4" placeholder="Matemática, Física, Redação..." required></textarea>

        <button type="submit">Gerar Cronograma no Google</button>
    </form>
</div>

</body>
</html>