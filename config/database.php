<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";  
$password = "";     
$dbname = "nexusplay_db";

// Crear conexión (INTENCIONALMENTE VULNERABLE)
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");

// Función para ejecutar consultas (SIN PROTECCIÓN - VULNERABLE)
function ejecutarConsulta($query) {
    global $conn;
    $result = $conn->query($query);
    
    // Mostrar errores SQL para facilitar la inyección
    if (!$result) {
        echo "<div style='color: red; background: #ffcccc; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
        echo "<strong>Error SQL:</strong> " . $conn->error . "<br>";
        echo "<strong>Consulta:</strong> " . htmlspecialchars($query);
        echo "</div>";
        return false;
    }
    
    return $result;
}

// Función para obtener datos (VULNERABLE)
function obtenerDatos($query) {
    $result = ejecutarConsulta($query);
    $datos = array();
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
    }
    
    return $datos;
}
?>

