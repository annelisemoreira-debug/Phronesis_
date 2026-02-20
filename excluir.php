<?php
session_start();
include("conexao.php");


if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    session_destroy();

    echo "Conta excluída com sucesso!";
    echo "<br><a href='login.php'>Voltar para login</a>";

} else {
    echo "Erro ao excluir conta!";
}
?>