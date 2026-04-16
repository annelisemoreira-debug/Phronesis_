<?php
try {
    // Tente com "" (vazio) primeiro, se falhar, tente "root"
    $link = new PDO("mysql:host=localhost;dbname=phronesis", "root", "Home@spSENAI2025!");
    echo "✅ Conexão funcionou perfeitamente!";
} catch (PDOException $e) {
    echo "❌ Erro ainda persiste: " . $e->getMessage();
}
?>