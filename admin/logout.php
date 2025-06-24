<?php
session_start();
require_once '../config/database.php';

// Registrar actividad de logout del panel admin si hay usuario logueado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log_query = "INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES ('$user_id', 'Logout del panel admin', '$ip')";
    ejecutarConsulta($log_query);
}

// Eliminar solo la verificación del panel admin, mantener la sesión principal
unset($_SESSION['admin_verified']);
unset($_SESSION['admin_login_time']);

// Redireccionar al login del panel admin
header("Location: login.php?msg=admin_logout");
exit();
?>