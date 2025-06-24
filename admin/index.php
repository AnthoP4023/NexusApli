<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit();
}

// Verificar si tiene acceso al panel admin
if (!isset($_SESSION['admin_verified'])) {
    header('Location: login.php');
    exit();
}

// Obtener sección actual
$section = $_GET['section'] ?? 'dashboard';

// Obtener información del administrador
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM usuarios WHERE id = '$admin_id'";
$admin_info = obtenerDatos($admin_query);
$admin = $admin_info[0] ?? null;

// Obtener estadísticas
$stats = [
    'usuarios' => contarRegistros("SELECT COUNT(*) as total FROM usuarios"),
    'juegos' => contarRegistros("SELECT COUNT(*) as total FROM videojuegos"),
    'noticias' => contarRegistros("SELECT COUNT(*) as total FROM noticias"),
    'ventas' => contarRegistros("SELECT COUNT(*) as total FROM compras"),
    'ingresos' => obtenerDatos("SELECT SUM(total) as ingresos FROM compras")[0]['ingresos'] ?? 0
];

// Obtener datos según la sección
$data = [];
switch($section) {
    case 'users':
        $data = obtenerDatos("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
        break;
    case 'transactions':
        $data = obtenerDatos("
            SELECT c.*, u.username, COUNT(dc.id) as items_comprados
            FROM compras c
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN detalle_compras dc ON c.id = dc.compra_id
            GROUP BY c.id
            ORDER BY c.fecha_compra DESC
        ");
        break;
    case 'activities':
        $data = obtenerDatos("
            SELECT l.*, u.username 
            FROM logs_actividad l 
            LEFT JOIN usuarios u ON l.usuario_id = u.id 
            ORDER BY l.fecha DESC 
            LIMIT 50
        ");
        break;
    default:
        // Dashboard - obtener datos recientes
        $data['recent_users'] = obtenerDatos("SELECT * FROM usuarios ORDER BY fecha_registro DESC LIMIT 5");
        $data['recent_transactions'] = obtenerDatos("
            SELECT c.*, u.username 
            FROM compras c
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            ORDER BY c.fecha_compra DESC 
            LIMIT 5
        ");
        $data['recent_activities'] = obtenerDatos("
            SELECT l.*, u.username 
            FROM logs_actividad l 
            LEFT JOIN usuarios u ON l.usuario_id = u.id 
            ORDER BY l.fecha DESC 
            LIMIT 10
        ");
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Nexus Play</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-panel">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🛡️</div>
                <h3 class="sidebar-title">NEXUS ADMIN</h3>
                <p class="sidebar-user"><?php echo htmlspecialchars($admin['username']); ?></p>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php?section=dashboard" class="nav-link <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="index.php?section=users" class="nav-link <?php echo $section === 'users' ? 'active' : ''; ?>">
                        <span class="nav-icon">👥</span>
                        Usuarios
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="index.php?section=transactions" class="nav-link <?php echo $section === 'transactions' ? 'active' : ''; ?>">
                        <span class="nav-icon">💰</span>
                        Transacciones
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="index.php?section=activities" class="nav-link <?php echo $section === 'activities' ? 'active' : ''; ?>">
                        <span class="nav-icon">📋</span>
                        Actividades
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <span class="nav-icon">⚙️</span>
                        Configuración
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="../admin/logout.php" class="nav-link">
                        <span class="nav-icon">🚪</span>
                        Cerrar Sesión
                    </a>
                </div>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <div class="admin-content">
            <?php if ($section === 'dashboard'): ?>
                <!-- Dashboard -->
                <div class="content-header">
                    <h1 class="content-title">📊 Dashboard</h1>
                    <p class="content-subtitle">Resumen general del sistema</p>
                </div>

                <!-- Estadísticas -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-icon">👥</div>
                        <div class="card-info">
                            <div class="card-number"><?php echo $stats['usuarios']; ?></div>
                            <div class="card-label">Usuarios Totales</div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">🎮</div>
                        <div class="card-info">
                            <div class="card-number"><?php echo $stats['juegos']; ?></div>
                            <div class="card-label">Videojuegos</div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">📰</div>
                        <div class="card-info">
                            <div class="card-number"><?php echo $stats['noticias']; ?></div>
                            <div class="card-label">Noticias</div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">💰</div>
                        <div class="card-info">
                            <div class="card-number"><?php echo $stats['ventas']; ?></div>
                            <div class="card-label">Ventas</div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">💵</div>
                        <div class="card-info">
                            <div class="card-number">$<?php echo number_format($stats['ingresos'], 2); ?></div>
                            <div class="card-label">Ingresos</div>
                        </div>
                    </div>
                </div>

                <!-- Últimas Actividades -->
                <div class="data-table">
                    <div class="table-header">
                        <h3 class="table-title">📋 Últimas Actividades</h3>
                    </div>
                    <div class="table-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>IP</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['recent_activities'])): ?>
                                    <?php foreach ($data['recent_activities'] as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['username'] ?? 'Sistema'); ?></td>
                                            <td><?php echo htmlspecialchars($activity['accion']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['ip_origen'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($activity['fecha'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center; color: #888;">No hay actividades</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($section === 'users'): ?>
                <!-- Usuarios -->
                <div class="content-header">
                    <h1 class="content-title">👥 Gestión de Usuarios</h1>
                    <p class="content-subtitle">Administrar usuarios del sistema</p>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3 class="table-title">Lista de Usuarios</h3>
                    </div>
                    <div class="table-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Edad</th>
                                    <th>Rol</th>
                                    <th>Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data)): ?>
                                    <?php foreach ($data as $user): ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($user['telefono'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php 
                                                if ($user['fecha_nacimiento']) {
                                                    $edad = date_diff(date_create($user['fecha_nacimiento']), date_create('today'))->y;
                                                    echo $edad . ' años';
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span style="color: <?php echo $user['rol'] === 'administrador' ? '#ff6b35' : '#4CAF50'; ?>">
                                                    <?php echo $user['rol'] === 'administrador' ? '🛡️' : '👤'; ?> <?php echo htmlspecialchars($user['rol']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($user['fecha_registro'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align: center; color: #888;">No hay usuarios</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($section === 'transactions'): ?>
                <!-- Transacciones -->
                <div class="content-header">
                    <h1 class="content-title">💰 Transacciones</h1>
                    <p class="content-subtitle">Historial de compras y ventas</p>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3 class="table-title">Historial de Compras</h3>
                    </div>
                    <div class="table-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data)): ?>
                                    <?php foreach ($data as $transaction): ?>
                                        <tr>
                                            <td>#<?php echo $transaction['id']; ?></td>
                                            <td><?php echo htmlspecialchars($transaction['username'] ?? 'Usuario eliminado'); ?></td>
                                            <td><?php echo $transaction['items_comprados']; ?> items</td>
                                            <td style="color: #4CAF50; font-weight: bold;">$<?php echo number_format($transaction['total'], 2); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($transaction['fecha_compra'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align: center; color: #888;">No hay transacciones</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($section === 'activities'): ?>
                <!-- Actividades -->
                <div class="content-header">
                    <h1 class="content-title">📋 Registro de Actividades</h1>
                    <p class="content-subtitle">Log completo de actividades del sistema</p>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3 class="table-title">Log de Actividades</h3>
                    </div>
                    <div class="table-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>IP Origen</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data)): ?>
                                    <?php foreach ($data as $activity): ?>
                                        <tr>
                                            <td>#<?php echo $activity['id']; ?></td>
                                            <td><?php echo htmlspecialchars($activity['username'] ?? 'Sistema'); ?></td>
                                            <td><?php echo htmlspecialchars($activity['accion']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['ip_origen'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($activity['fecha'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align: center; color: #888;">No hay actividades registradas</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>