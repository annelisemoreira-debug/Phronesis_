<?php
// pages/atualizar_progresso.php
// Recebe (POST): id_cronograma, id_materia, id_topico, status (0|1)
// Responde (JSON): { sucesso, porcentagemMateria, porcentagemGeral } ou { sucesso:false, erro }
session_start();
header('Content-Type: application/json; charset=utf-8');

function responder(array $dados, int $http = 200): void
{
    http_response_code($http);
    echo json_encode($dados);
    exit;
}

if (!isset($_SESSION['id'])) {
    responder(['sucesso' => false, 'erro' => 'Não autenticado'], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['sucesso' => false, 'erro' => 'Método inválido'], 405);
}

include '../conexao.php'; // fornece $conn (mysqli)

$idUsuario    = intval($_SESSION['id']);
$idCronograma = intval($_POST['id_cronograma'] ?? 0);
$idMateria    = intval($_POST['id_materia'] ?? 0);
$idTopico     = intval($_POST['id_topico'] ?? 0);
$status       = (intval($_POST['status'] ?? 0) === 1) ? 1 : 0;

if (!$idCronograma || !$idMateria || !$idTopico) {
    responder(['sucesso' => false, 'erro' => 'Parâmetros inválidos'], 400);
}

try {
    // 1. PERMISSÃO: o cronograma precisa pertencer à conta Google vinculada ao usuário logado
    $st = $conn->prepare(
        "SELECT 1
           FROM cronograma c
           JOIN usuario u ON u.google_email = c.google_email
          WHERE c.id_cronograma = ? AND u.id_usuario = ?
          LIMIT 1"
    );
    $st->bind_param('ii', $idCronograma, $idUsuario);
    $st->execute();
    $permitido = $st->get_result()->fetch_row();
    $st->close();

    if (!$permitido) {
        responder(['sucesso' => false, 'erro' => 'Sem permissão para este cronograma'], 403);
    }

    // 2. O tópico precisa pertencer à matéria informada
    $st = $conn->prepare("SELECT 1 FROM materia_topico WHERE id_topico = ? AND id_materia = ?");
    $st->bind_param('ii', $idTopico, $idMateria);
    $st->execute();
    $topicoOk = $st->get_result()->fetch_row();
    $st->close();

    if (!$topicoOk) {
        responder(['sucesso' => false, 'erro' => 'Tópico inválido'], 400);
    }

    // 3. Grava o status (chave única: cronograma + matéria + tópico)
    $st = $conn->prepare(
        "INSERT INTO cronograma_topico_status (id_cronograma, id_materia, id_topico, status)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE status = VALUES(status)"
    );
    $st->bind_param('iiii', $idCronograma, $idMateria, $idTopico, $status);
    $st->execute();
    $st->close();

    // 4. Porcentagem da matéria
    $st = $conn->prepare(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN s.status = 1 THEN 1 ELSE 0 END) AS feitos
           FROM materia_topico t
      LEFT JOIN cronograma_topico_status s
             ON s.id_topico = t.id_topico AND s.id_cronograma = ?
          WHERE t.id_materia = ?"
    );
    $st->bind_param('ii', $idCronograma, $idMateria);
    $st->execute();
    $m = $st->get_result()->fetch_assoc();
    $st->close();
    $porcentagemMateria = ($m['total'] > 0) ? round(($m['feitos'] / $m['total']) * 100) : 0;

    // 5. Porcentagem geral (todas as matérias presentes no cronograma)
    $st = $conn->prepare(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN s.status = 1 THEN 1 ELSE 0 END) AS feitos
           FROM materia_topico t
      LEFT JOIN cronograma_topico_status s
             ON s.id_topico = t.id_topico AND s.id_cronograma = ?
          WHERE t.id_materia IN (SELECT DISTINCT id_materia FROM cronograma_item WHERE id_cronograma = ?)"
    );
    $st->bind_param('ii', $idCronograma, $idCronograma);
    $st->execute();
    $g = $st->get_result()->fetch_assoc();
    $st->close();
    $porcentagemGeral = ($g['total'] > 0) ? round(($g['feitos'] / $g['total']) * 100) : 0;

    responder([
        'sucesso'            => true,
        'porcentagemMateria' => $porcentagemMateria,
        'porcentagemGeral'   => $porcentagemGeral
    ]);
} catch (Throwable $e) {
    responder(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()], 500);
}