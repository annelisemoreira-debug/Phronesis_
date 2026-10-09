<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Usuário não autorizado']);
    exit();
}

include '../conexao.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Falha na conexão com o banco']);
    exit();
}

$id_usuario = (int) $_SESSION['id'];
$video_id   = isset($_POST['v_id']) ? trim($_POST['v_id']) : '';

if (!preg_match('/^[A-Za-z0-9_-]{6,20}$/', $video_id)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'ID do vídeo inválido']);
    exit();
}

$stmt = $conn->prepare("SELECT id_visto FROM vistos WHERE id_usuario = ? AND video_id = ?");
$stmt->bind_param("is", $id_usuario, $video_id);
$stmt->execute();
$existe = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($existe) {
    $stmt = $conn->prepare("DELETE FROM vistos WHERE id_usuario = ? AND video_id = ?");
    $stmt->bind_param("is", $id_usuario, $video_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true, 'status' => 'removido']);
} else {
    $stmt = $conn->prepare("INSERT INTO vistos (id_usuario, video_id) VALUES (?, ?)");
    $stmt->bind_param("is", $id_usuario, $video_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true, 'status' => 'adicionado']);
}

$conn->close();
exit();