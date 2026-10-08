<?php
// livros_acao.php  (fica na mesma pasta do livros.php)
session_start();
header('Content-Type: application/json; charset=utf-8');

function resposta($ok, $extra = [], $http = 200) {
    http_response_code($http);
    echo json_encode(array_merge(['ok' => $ok], $extra));
    exit();
}

if (!isset($_SESSION['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(false, ['erro' => 'Não autorizado'], 403);
}

include '../conexao.php';

$uid   = (int) $_SESSION['id'];
$idl   = (int) ($_POST['id_livro'] ?? 0);
$acao  = $_POST['acao'] ?? '';

// O livro precisa existir
$stmt = $conn->prepare("SELECT 1 FROM livro WHERE id_livro = ?");
$stmt->bind_param("i", $idl);
$stmt->execute();
if (!$stmt->get_result()->fetch_row()) {
    resposta(false, ['erro' => 'Livro não encontrado'], 404);
}
$stmt->close();

if ($acao === 'favoritar') {
    $stmt = $conn->prepare("SELECT 1 FROM livro_favorito WHERE id_usuario = ? AND id_livro = ?");
    $stmt->bind_param("ii", $uid, $idl);
    $stmt->execute();
    $existe = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if ($existe) {
        $stmt = $conn->prepare("DELETE FROM livro_favorito WHERE id_usuario = ? AND id_livro = ?");
    } else {
        $stmt = $conn->prepare("INSERT INTO livro_favorito (id_usuario, id_livro) VALUES (?, ?)");
    }
    $stmt->bind_param("ii", $uid, $idl);
    $stmt->execute();
    resposta(true, ['favorito' => !$existe]);
}

if ($acao === 'progresso') {
    $status = $_POST['status'] ?? '';

    if ($status === 'nao_iniciada') {
        $stmt = $conn->prepare("DELETE FROM livro_progresso WHERE id_usuario = ? AND id_livro = ?");
        $stmt->bind_param("ii", $uid, $idl);
        $stmt->execute();
        resposta(true, ['status' => '']);
    }

    if (in_array($status, ['em_andamento', 'concluida'], true)) {
        $stmt = $conn->prepare(
            "INSERT INTO livro_progresso (id_usuario, id_livro, status) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status)"
        );
        $stmt->bind_param("iis", $uid, $idl, $status);
        $stmt->execute();
        resposta(true, ['status' => $status]);
    }
}

resposta(false, ['erro' => 'Ação inválida'], 400);