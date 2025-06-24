<?php
session_start();
require_once 'config/database.php';

// Registrar actividad de logout si hay usuario logueado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log_query = "INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES ('$user_id', 'Logout exitoso', '$ip')";
    ejecutarConsulta($log_query);
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borra también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redireccionar al login con mensaje
header("Location: login.php?msg=logout");
exit();
?>