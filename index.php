<?php
// AGREGAR ESTO AL INICIO DE INDEX.PHP
session_start();

$page_title = "Nexus Play - Gaming Store";
require_once 'config/database.php';
require_once 'includes/header.php';

// Obtener parámetro de búsqueda desde la URL (vulnerable)
$search = isset($_GET['q']) ? $_GET['q'] : '';

// Consulta vulnerable (si hay ?q= en la URL)
if (!empty($search)) {
    $query = "SELECT * FROM videojuegos WHERE titulo LIKE '%$search%' OR genero LIKE '%$search%' ORDER BY 1";
    $juegos_populares = obtenerDatos($query);
} else {
    // Consulta segura por defecto
    $query = "SELECT v.*, (50 - v.stock) as vendidos FROM videojuegos v ORDER BY vendidos DESC LIMIT 6";
    $juegos_populares = obtenerDatos($query);
}

// Obtener últimas noticias
$query_noticias = "SELECT * FROM noticias ORDER BY fecha_publicacion DESC LIMIT 4";
$noticias = obtenerDatos($query_noticias);

// Mapear imágenes de noticias por ID
$imagenes_noticias = [
    1 => 'zelda.jpeg',
    2 => 'descuento.jpg',
    3 => 'minecraf_noticia.jpg',
    4 => 'E3.jpg',
    5 => 'cod_news.jpg',
    6 => 'playstation_news.jpg',
    7 => 'epic_games.jpg',
    8 => 'fortnite_marvel.jpg',
    9 => 'valorant_news.jpg',
    10 => 'nintendo_direct.jpg',
    11 => 'lol_worlds.jpg',
    12 => 'amd_gpu.jpg'
];

// Mapear imágenes de juegos por ID
$imagenes_juegos = [
    1 => 'SonicFrontiers.jpg',
    2 => 'MarioKart8Deluxe.jpg',
    3 => 'TheWitcher3.jpg',
    4 => 'Minecraft.jpg',
    5 => 'FIFA24.jpg',
    6 => 'CallOfDuty.jpg',
    7 => 'Cyberpunk2077.jpg',
    8 => 'RedDeadRedemption2.jpg',
    9 => 'GTAV.jpg',
    10 => 'Fortnite.jpg',
    11 => 'AmongUs.jpg',
    12 => 'FallGuys.jpg',
    13 => 'Valorant.jpg',
    14 => 'LeagueOfLegends.jpg',
    15 => 'ApexLegends.jpg',
    16 => 'Overwatch2.jpg',
    17 => 'RocketLeague.jpg',
    18 => 'AssassinsCreedValhalla.jpg',
    19 => 'SpiderMan.jpg',
    20 => 'GodOfWar.jpg',
    21 => 'HorizonZeroDawn.jpg',
    22 => 'DeathStranding.jpg',
    23 => 'EldenRing.jpg',
    24 => 'Hades.jpg',
    25 => 'StardewValley.jpg'
];
?>

<main class="main-content">
    <!-- Sección de Noticias -->
    <section class="news-section">
        <h2 class="section-title">📰 Últimas Noticias</h2>
        <div class="news-grid">
            <?php if (!empty($noticias)): ?>
                <?php foreach ($noticias as $noticia): ?>
                    <div class="news-card">
                        <?php 
                        $imagen_noticia = isset($imagenes_noticias[$noticia['id']]) ? $imagenes_noticias[$noticia['id']] : 'default.jpg';
                        ?>
                        <div class="news-image">
                            <img src="images/noticias/<?php echo $imagen_noticia; ?>" alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
                        </div>
                        <h3 class="news-title"><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                        <p class="news-content">
                            <?php echo substr(htmlspecialchars($noticia['contenido']), 0, 150) . '...'; ?>
                        </p>
                        <p class="news-date">
                            <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
                        </p>
                        <a href="news.php?id=<?php echo $noticia['id']; ?>">Leer más →</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay noticias disponibles.</p>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="news.php" style="background: #ff6b35; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Ver todas las noticias</a>
        </div>
    </section>

    <!-- Sección de Juegos Más Comprados -->
    <section class="games-section">
        <h2 class="section-title">🎮 Juegos Más Comprados</h2>
        <div class="games-grid">
            <?php if (!empty($juegos_populares)): ?>
                <?php foreach ($juegos_populares as $juego): ?>
                    <div class="game-card">
                        <?php 
                        $imagen_juego = isset($imagenes_juegos[$juego['id']]) ? $imagenes_juegos[$juego['id']] : 'default.jpg';
                        ?>
                        <div class="game-image">
                            <img src="images/juegos/<?php echo $imagen_juego; ?>" alt="<?php echo htmlspecialchars($juego['titulo']); ?>">
                        </div>
                        <div class="game-info">
                            <h3 class="game-title"><?php echo htmlspecialchars($juego['titulo']); ?></h3>
                            <p class="game-genre"><?php echo htmlspecialchars($juego['genero']); ?></p>
                            <p class="game-price">$<?php echo number_format($juego['precio'] ?? 0, 2); ?></p>
                            <?php if (isset($juego['vendidos'])): ?>
                                <p style="color: #888; font-size: 12px;">
                                    Vendidos: <?php echo $juego['vendidos'] ?? 0; ?> | Stock: <?php echo $juego['stock'] ?? 0; ?>
                                </p>
                            <?php endif; ?>
                            <div class="game-buttons">
                                <a href="games.php?id=<?php echo $juego['id']; ?>" class="btn btn-details">Ver Detalles</a>
                                <a href="cart.php?action=add&id=<?php echo $juego['id']; ?>" class="btn btn-cart">Añadir Carrito</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay juegos disponibles.</p>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="games.php" style="background: #ff6b35; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Ver todos los juegos</a>
        </div>
    </section>

    <!-- Información de SQLi para testing -->
    <?php if (!empty($search)): ?>
        <section style="background: #2d2d2d; padding: 20px; border-radius: 10px; margin-top: 30px;">
            <h3 style="color: #ff6b35;">🔍 Búsqueda realizada: "<?php echo htmlspecialchars($search); ?>"</h3>
            <div style="background: #1a1a1a; padding: 15px; border-radius: 5px; margin-top: 10px;">
                <h4 style="color: #4CAF50;">💡 Tips para SQLi Testing:</h4>
                <ul style="color: #ccc;">
                    <li>Prueba: <code style="background: #404040; padding: 2px 5px; border-radius: 3px;">' OR '1'='1</code></li>
                    <li>Union: <code style="background: #404040; padding: 2px 5px; border-radius: 3px;">' UNION SELECT 1,2,3,4,5,6--</code></li>
                    <li>Database info: <code style="background: #404040; padding: 2px 5px; border-radius: 3px;">' UNION SELECT database(),user(),version(),4,5,6--</code></li>
                    <li>Tables: <code style="background: #404040; padding: 2px 5px; border-radius: 3px;">' UNION SELECT table_name,2,3,4,5,6 FROM information_schema.tables--</code></li>
                </ul>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>