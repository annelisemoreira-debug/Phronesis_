<?php

// --- CONEXÃO ---
$servername = "localhost";
$username = "root";
$password = "Home@spSENAI2025!";
$dbname = "phronesis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Erro: " . $conn->connect_error); }

?>