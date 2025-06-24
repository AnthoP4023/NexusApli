<?php
// Mostrar todos los errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
$servername = "localhost";
$username = "root";  
$password = "";     
$dbname = "nexusplay_db4";

// Crear conexión mysqli (INTENCIONALMENTE VULNERABLE)
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
    
    // Mostrar la consulta SQL para debugging
    echo "<!-- DEBUG SQL: " . htmlspecialchars($query) . " -->";
    
    $result = $conn->query($query);
    
    // Mostrar errores SQL para facilitar la inyección
    if ($result === false) {
        echo "<div style='color: red; background: #ffcccc; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
        echo "<strong>Error SQL:</strong> " . $conn->error . "<br>";
        echo "<strong>Consulta:</strong> " . htmlspecialchars($query) . "<br>";
        echo "<strong>Archivo:</strong> " . basename($_SERVER['PHP_SELF']) . "<br>";
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

// Función para obtener un solo registro
function obtenerUnDato($query) {
    $result = ejecutarConsulta($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

// Función para contar registros
function contarRegistros($query) {
    $result = ejecutarConsulta($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return array_values($row)[0];
    }
    
    return 0;
}

// Función para insertar y obtener el último ID
function insertarYObtenerID($query) {
    global $conn;
    $result = ejecutarConsulta($query);
    
    if ($result) {
        return $conn->insert_id;
    }
    
    return false;
}
?>