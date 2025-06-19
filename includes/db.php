<?php
// includes/db.php - Conexión a la base de datos MySQL
$host = 'localhost';
$dbname = 'nexusplay_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función vulnerable para ejecutar consultas directas
function executeQuery($sql) {
    global $pdo;
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Mostrar errores SQL para facilitar SQLi error-based
        echo "<div style='color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; border: 1px solid red;'>";
        echo "<strong>SQL Error:</strong> " . $e->getMessage();
        echo "</div>";
        return false;
    }
}
?>