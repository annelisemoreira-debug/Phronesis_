<?php
try {
    // Tente com "" (vazio) primeiro, se falhar, tente "root"
    // --- SENHA PARA CONEXÃO: Home@spSENAI2025! ---
    $link = new PDO("mysql:host=localhost;dbname=phronesis", "root", "");
    echo "✅ Conexão funcionou perfeitamente!";
} catch (PDOException $e) {
    echo "❌ Erro ainda persiste: " . $e->getMessage();
}
?>