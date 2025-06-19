<?php
session_start();
$page_title = "Panel de Administrador - Nexus Play";
require_once 'config/database.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

// Obtener información del administrador
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM usuarios WHERE id = '$admin_id'";
$admin_info = obtenerDatos($admin_query);
$admin = $admin_info[0] ?? null;

// Obtener estadísticas del sistema
$total_usuarios = obtenerDatos("SELECT COUNT(*) as total FROM usuarios")[0]['total'] ?? 0;
$total_juegos = obtenerDatos("SELECT COUNT(*) as total FROM videojuegos")[0]['total'] ?? 0;
$total_noticias = obtenerDatos("SELECT COUNT(*) as total FROM noticias")[0]['total'] ?? 0;
$total_ventas = obtenerDatos("SELECT COUNT(*) as total FROM compras")[0]['total'] ?? 0;
$ingresos_total = obtenerDatos("SELECT SUM(total) as ingresos FROM compras")[0]['ingresos'] ?? 0;

// Obtener últimas actividades del sistema
$ultimas_actividades = obtenerDatos("
    SELECT l.*, u.username 
    FROM logs_actividad l 
    LEFT JOIN usuarios u ON l.usuario_id = u.id 
    ORDER BY l.fecha DESC 
    LIMIT 10
");

// Obtener últimas compras
$ultimas_compras = obtenerDatos("
    SELECT c.*, u.username, COUNT(dc.id) as items_comprados
    FROM compras c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN detalle_compras dc ON c.id = dc.compra_id
    GROUP BY c.id
    ORDER BY c.fecha_compra DESC
    LIMIT 5
");

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="css/admin.css">

<main class="admin-container">
    <!-- Header del Panel -->
    <div class="admin-header">
        <h1>🛡️ Panel de Administrador</h1>
        <p>Bienvenido, <strong><?php echo htmlspecialchars($admin['username']); ?></strong></p>
    </div>

    <!-- Perfil del Administrador -->
    <section class="admin-profile-section">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <span class="avatar-icon">👤</span>
                </div>
                <div class="profile-info">
                    <h2>Mi Perfil de Administrador</h2>
                    <p class="profile-role">🛡️ Administrador del Sistema</p>
                </div>
            </div>
            
            <div class="profile-details">
                <div class="detail-row">
                    <span class="detail-label">👤 Usuario:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">📧 Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($admin['email'] ?? 'No especificado'); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">🔐 Rol:</span>
                    <span class="detail-value role-badge"><?php echo htmlspecialchars($admin['rol']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">📅 Fecha de Registro:</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($admin['fecha_registro'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">🆔 ID de Usuario:</span>
                    <span class="detail-value"><?php echo $admin['id']; ?></span>
                </div>
            </div>
            
            <!-- Botón para ir a la página de gestión completa -->
            <div class="profile-actions">
                <a href="admin_manage.php" class="btn-manage">
                    ⚙️ Gestionar Sistema Completo
                </a>
                <a href="index.php" class="btn-back">
                    🏠 Volver al Inicio
                </a>
            </div>
        </div>
    </section>

    <!-- Estadísticas del Sistema -->
    <section class="stats-section">
        <h2 class="section-title">📊 Estadísticas del Sistema</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_usuarios; ?></div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎮</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_juegos; ?></div>
                    <div class="stat-label">Videojuegos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📰</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_noticias; ?></div>
                    <div class="stat-label">Noticias Publicadas</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_ventas; ?></div>
                    <div class="stat-label">Ventas Totales</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💵</div>
                <div class="stat-info">
                    <div class="stat-number">$<?php echo number_format($ingresos_total, 2); ?></div>
                    <div class="stat-label">Ingresos Generados</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Últimas Compras -->
    <section class="recent-section">
        <h2 class="section-title">🛒 Últimas Compras</h2>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Items</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ultimas_compras)): ?>
                        <?php foreach ($ultimas_compras as $compra): ?>
                            <tr>
                                <td>#<?php echo $compra['id']; ?></td>
                                <td><?php echo htmlspecialchars($compra['username'] ?? 'Usuario eliminado'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha_compra'])); ?></td>
                                <td><?php echo $compra['items_comprados']; ?> items</td>
                                <td class="price">$<?php echo number_format($compra['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">No hay compras registradas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Últimas Actividades -->
    <section class="recent-section">
        <h2 class="section-title">📋 Últimas Actividades del Sistema</h2>
        <div class="activity-list">
            <?php if (!empty($ultimas_actividades)): ?>
                <?php foreach ($ultimas_actividades as $actividad): ?>
                    <div class="activity-item">
                        <div class="activity-icon">📝</div>
                        <div class="activity-info">
                            <div class="activity-text">
                                <strong><?php echo htmlspecialchars($actividad['username'] ?? 'Sistema'); ?></strong>
                                - <?php echo htmlspecialchars($actividad['accion']); ?>
                            </div>
                            <div class="activity-meta">
                                <?php echo date('d/m/Y H:i', strtotime($actividad['fecha'])); ?>
                                <?php if ($actividad['ip_origen']): ?>
                                    | IP: <?php echo htmlspecialchars($actividad['ip_origen']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-activity">
                    <p>No hay actividades registradas</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Accesos Rápidos -->
    <section class="quick-actions">
        <h2 class="section-title">⚡ Accesos Rápidos</h2>
        <div class="quick-grid">
            <a href="admin_manage.php?section=users" class="quick-card">
                <div class="quick-icon">👥</div>
                <div class="quick-label">Gestionar Usuarios</div>
            </a>
            
            <a href="admin_manage.php?section=games" class="quick-card">
                <div class="quick-icon">🎮</div>
                <div class="quick-label">Gestionar Juegos</div>
            </a>
            
            <a href="admin_manage.php?section=news" class="quick-card">
                <div class="quick-icon">📰</div>
                <div class="quick-label">Gestionar Noticias</div>
            </a>
            
            <a href="admin_manage.php?section=sales" class="quick-card">
                <div class="quick-icon">💰</div>
                <div class="quick-label">Ver Ventas</div>
            </a>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>