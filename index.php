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
    $juegos_populares = obtenerDatos("
        SELECT * FROM videojuegos 
        WHERE titulo LIKE '%$search%' 
        OR genero LIKE '%$search%' 
        ORDER BY 1
    ");
} else {
    // Consulta segura por defecto
    $juegos_populares = obtenerDatos("
        SELECT v.*, (50 - v.stock) as vendidos 
        FROM videojuegos v 
        ORDER BY vendidos DESC 
        LIMIT 6
    ");
}

// Obtener últimas noticias
$noticias = obtenerDatos("SELECT * FROM noticias ORDER BY fecha_publicacion DESC LIMIT 4");

// Mapear imágenes de noticias por ID
$imagenes_noticias = [
    1 => 'zelda.jpeg',
    2 => 'descuento.jpg',
    3 => 'minecraf_noticia.jpg',
    4 => 'E3.jpg'
];

// Mapear imágenes de juegos por ID
$imagenes_juegos = [
    1 => 'SonicFrontiers.jpg',
    2 => 'MarioKart8Deluxe.jpg',
    3 => 'TheWitcher3.jpg',
    4 => 'Minecraft.jpg',
    5 => 'FIFA24.jpg'
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
                            <p class="game-price">$<?php echo number_format($juego['precio'], 2); ?></p>
                            <p style="color: #888; font-size: 12px;">
                                Vendidos: <?php echo $juego['vendidos']; ?> | Stock: <?php echo $juego['stock']; ?>
                            </p>
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
</main>

<?php require_once 'includes/footer.php'; ?>