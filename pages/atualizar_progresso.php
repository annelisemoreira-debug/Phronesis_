<?php
header('Content-Type: application/json');
require_once '../conexao.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONEXÃO COM O BANCO VIA PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=phronesis;charset=utf8", "root", "Home@spSENAI2025!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}

$id_cronograma = filter_input(INPUT_POST, 'id_cronograma', FILTER_VALIDATE_INT);
$id_materia    = filter_input(INPUT_POST, 'id_materia', FILTER_VALIDATE_INT);
$id_topico     = filter_input(INPUT_POST, 'id_topico', FILTER_VALIDATE_INT);
$status        = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);

$id_usuario = isset($_SESSION['id']) ? intval($_SESSION['id']) : null; 

if (!$id_cronograma || !$id_materia || !$id_topico || $status === null || !$id_usuario) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos ou usuário não autenticado.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ATUALIZADO: Agora usa a tabela correta 'cronograma_topico_status'
    if ($status === 1) {
        $stmt = $pdo->prepare("INSERT INTO cronograma_topico_status (id_cronograma, id_materia, id_topico, status) 
                               VALUES (:id_cronograma, :id_materia, :id_topico, 1)
                               ON DUPLICATE KEY UPDATE status = 1");
    } else {
        $stmt = $pdo->prepare("DELETE FROM cronograma_topico_status 
                               WHERE id_cronograma = :id_cronograma AND id_materia = :id_materia AND id_topico = :id_topico");
    }

    $params = [
        ':id_cronograma' => $id_cronograma,
        ':id_materia'    => $id_materia,
        ':id_topico'     => $id_topico
    ];

    $stmt->execute($params);

    // 1. Total de tópicos existentes para esta matéria específica (Ajuste o nome da tabela se não for 'cod_topicos')
    $stmtTotalMateria = $pdo->prepare("SELECT COUNT(*) FROM materia_topico WHERE id_materia = :id_materia");
    $stmtTotalMateria->execute([':id_materia' => $id_materia]);
    $totalTopicosMateria = (int)$stmtTotalMateria->fetchColumn();

    // 2. Quantos estão marcados como concluídos (status = 1) nesta matéria
    $stmtConcluidosMateria = $pdo->prepare("SELECT COUNT(*) FROM cronograma_topico_status 
                                            WHERE id_cronograma = :id_cronograma AND id_materia = :id_materia AND status = 1");
    $stmtConcluidosMateria->execute([':id_cronograma' => $id_cronograma, ':id_materia' => $id_materia]);
    $concluidosMateria = (int)$stmtConcluidosMateria->fetchColumn();

    $porcentagemMateria = $totalTopicosMateria > 0 ? ($concluidosMateria / $totalTopicosMateria) * 100 : 0;

    // 3. Porcentagem Geral do Cronograma (Total de tópicos de todas as matérias envolvidas neste cronograma)
    $stmtTotalGeral = $pdo->prepare("SELECT COUNT(*) FROM materia_topico WHERE id_materia IN (SELECT DISTINCT id_materia FROM cronograma_topico_status WHERE id_cronograma = :id_cronograma)");
    $stmtTotalGeral->execute([':id_cronograma' => $id_cronograma]);
    $totalTopicosGeral = (int)$stmtTotalGeral->fetchColumn();

    if ($totalTopicosGeral === 0) {
        $totalTopicosGeral = $totalTopicosMateria > 0 ? $totalTopicosMateria : 1; 
    }

    // 4. Quantos estão concluídos no cronograma inteiro
    $stmtConcluidosGeral = $pdo->prepare("SELECT COUNT(*) FROM cronograma_topico_status 
                                          WHERE id_cronograma = :id_cronograma AND status = 1");
    $stmtConcluidosGeral->execute([':id_cronograma' => $id_cronograma]);
    $concluidosGeral = (int)$stmtConcluidosGeral->fetchColumn();

    $porcentagemGeral = $totalTopicosGeral > 0 ? ($concluidosGeral / $totalTopicosGeral) * 100 : 0;

    $pdo->commit();

    echo json_encode([
        'sucesso'            => true,
        'porcentagemMateria' => round($porcentagemMateria, 2),
        'porcentagemGeral'   => round($porcentagemGeral, 2)
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno no servidor: ' . $e->getMessage()]);
}