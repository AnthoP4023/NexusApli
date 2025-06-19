<?php
// AGREGAR ESTO AL INICIO DEL HEADER
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Nexus Play - Gaming Store'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($auth_page) && $auth_page === true): ?>
    <link rel="stylesheet" href="css/auth.css">
    <?php endif; ?>
</head>
<body>
    <?php if (!isset($simple_header) || $simple_header !== true): ?>
    <header class="header">
        <div class="logo">
            <h1><a href="index.php" style="color: #ff6b35; text-decoration: none;">NEXUSPLAY</a></h1>
        </div>
        
        <div class="header-right">
            <?php if (!isset($hide_search) || $hide_search !== true): ?>
            <!-- Buscador -->
            <form class="search-form" action="search.php" method="GET">
                <input type="text" name="q" class="search-input" placeholder="Buscar juegos o noticias...">
                <button type="submit" class="search-btn">🔍</button>
            </form>
            <?php endif; ?>
            
            <!-- Iconos de navegación -->
            <div class="header-icons">
                <a href="news.php" class="icon-link" title="Noticias">📰</a>
                <a href="cart.php" class="icon-link" title="Juegos">🎮</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Usuario logueado -->
                    <?php if ($_SESSION['rol'] === 'administrador'): ?>
                        <a href="admin.php" class="icon-link" title="Admin">👤</a>
                    <?php endif; ?>
                    <a href="login.php?logout=1" class="icon-link" title="Cerrar Sesión">🚪</a>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <a href="login.php" class="icon-link" title="Login">👤</a>
                <?php endif; ?>
                
            </div>
        </div>
    </header>
    <?php else: ?>
    <!-- Header simplificado solo para login/register -->
    <header class="simple-header">
        <div class="logo">
            <h1><a href="index.php" style="color: #ff6b35; text-decoration: none;">NEXUS PLAY</a></h1>
        </div>
    </header>
    <?php endif; ?>