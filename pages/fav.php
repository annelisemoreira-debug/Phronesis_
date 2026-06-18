<?php
session_start();
include '../conexao.php';

if (isset($_POST['v_id']) && isset($_SESSION['id'])) {
    $v_id = $_POST['v_id'];
    $v_tit = $_POST['v_tit'];
    $u_id = $_SESSION['id'];

    // Verifica se já existe para alternar (Add/Remover)
    $check = $conn->query("SELECT id_favorito FROM favoritos WHERE video_id = '$v_id' AND id_usuario = $u_id");

    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM favoritos WHERE video_id = '$v_id' AND id_usuario = $u_id");
    } else {
        $conn->query("INSERT INTO favoritos (id_usuario, video_id, titulo_video) VALUES ($u_id, '$v_id', '$v_tit')");
    }
}
?>