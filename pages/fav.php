<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Usuário não autorizado']);
    exit();
}

include '../conexao.php';

$u_id  = (int) $_SESSION['id'];
$v_id  = isset($_POST['v_id'])  ? trim($_POST['v_id'])  : '';
$v_tit = isset($_POST['v_tit']) ? trim($_POST['v_tit']) : '';

// IDs do YouTube: 11 caracteres [A-Za-z0-9_-]
if (!preg_match('/^[A-Za-z0-9_-]{6,20}$/', $v_id)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'ID do vídeo inválido']);
    exit();
}

$v_tit = mb_substr($v_tit, 0, 255, 'UTF-8');

$stmt = $conn->prepare("SELECT id_favorito FROM favoritos WHERE video_id = ? AND id_usuario = ?");
$stmt->bind_param("si", $v_id, $u_id);
$stmt->execute();
$existe = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($existe) {
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE video_id = ? AND id_usuario = ?");
    $stmt->bind_param("si", $v_id, $u_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true, 'favorito' => false]);
} else {
    $stmt = $conn->prepare("INSERT INTO favoritos (id_usuario, video_id, titulo_video) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $u_id, $v_id, $v_tit);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true, 'favorito' => true]);
}

$conn->close();
exit();