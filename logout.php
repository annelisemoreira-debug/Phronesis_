<?php
session_start();
session_destroy(); // Mata todas as variáveis de sessão
header("Location: index.php"); // Volta para a home
exit();
?>