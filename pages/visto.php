<?php
session_start();

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["erro" => "Usuário não autorizado"]);
    exit();
}

include '../conexao.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["erro" => "Falha na conexão com o banco"]);
    exit();
}

// 3. Verifica se recebeu o ID do vídeo
if (isset($_POST['v_id'])) {
    $id_usuario = $_SESSION['id'];
    $video_id = $conn->real_escape_string($_POST['v_id']);

    // 4. Verifica se este vídeo JÁ FOI marcado como visto por este usuário
    $check = $conn->query("SELECT id_visto FROM vistos WHERE id_usuario = $id_usuario AND video_id = '$video_id'");

    if ($check && $check->num_rows > 0) {
        // Se já existe no banco, significa que o usuário quer DESMARCAR o visto (Igual ao favorito)
        $conn->query("DELETE FROM vistos WHERE id_usuario = $id_usuario AND video_id = '$video_id'");
        echo json_encode(["status" => "removido"]);
    } else {
        // Se não existe, insere no banco para salvar como visto permanentemente
        $conn->query("INSERT INTO vistos (id_usuario, video_id) VALUES ($id_usuario, '$video_id')");
        echo json_encode(["status" => "adicionado"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["erro" => "ID do vídeo não fornecido"]);
}

$conn->close();
exit();