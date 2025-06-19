<?php
$page_title = "Búsqueda - Nexus Play";
$hide_search = true; // Ocultar buscador del header en esta página
require_once 'config/database.php';
require_once 'includes/header.php';

// Obtener término de búsqueda (VULNERABLE - Sin sanitización)
$search_term = isset($_GET['q']) ? $_GET['q'] : '';

$resultados_juegos = [];
$resultados_noticias = [];
$error_sql = '';

if (!empty($search_term)) {

    $query_juegos = "SELECT titulo, descripcion, genero, id, precio FROM videojuegos WHERE titulo LIKE '%$search_term%' OR descripcion LIKE '%$search_term%' OR genero LIKE '%$search_term%'";

    echo "<!-- DEBUG Query Juegos: $query_juegos -->";
    $resultados_juegos = obtenerDatos($query_juegos);
    
    $query_noticias = "
        SELECT n.titulo, n.contenido, u.username AS autor, n.id, n.fecha_publicacion
        FROM noticias n 
        LEFT JOIN usuarios u ON n.autor_id = u.id 
        WHERE n.titulo LIKE '%$search_term%' OR n.contenido LIKE '%$search_term%'
    ";
    echo "<!-- DEBUG Query Noticias: $query_noticias -->";
    $resultados_noticias = obtenerDatos($query_noticias);
}

// Mapear imágenes
$imagenes_juegos = [
    1 => 'SonicFrontiers.jpg',
    2 => 'MarioKart8Deluxe.jpg', 
    3 => 'TheWitcher3.jpg',
    4 => 'Minecraft.jpg',
    5 => 'FIFA24.jpg'
];

$imagenes_noticias = [
    1 => 'zelda.jpeg',
    2 => 'descuento.jpg',
    3 => 'minecraf_noticia.jpg',
    4 => 'E3.jpg'
];
?>

<main class="main-content">
    <div style="background: #2d2d2d; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
        <h1 style="color: #ff6b35; margin-bottom: 15px;">🔍 Búsqueda</h1>
        
        <!-- Formulario de búsqueda -->
        <form method="GET" style="margin-bottom: 20px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" 
                       name="q" 
                       value="<?php echo htmlspecialchars($search_term); ?>" 
                       placeholder="Buscar juegos, noticias..." 
                       style="flex: 1; padding: 12px; border: none; border-radius: 5px; background: #404040; color: white;">
                <button type="submit" 
                        style="padding: 12px 20px; border: none; border-radius: 5px; background: #ff6b35; color: white; cursor: pointer;">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($search_term)): ?>
        
        <!-- Resultados de Juegos -->
        <section class="games-section">
            <h2 class="section-title">🎮 Juegos Encontrados (<?php echo count($resultados_juegos); ?>)</h2>
            
            <?php if (!empty($resultados_juegos)): ?>
                <div class="games-grid">
                    <?php foreach ($resultados_juegos as $juego): ?>
                        <div class="game-card">
                            <?php 
                            $imagen_juego = isset($imagenes_juegos[$juego['id']]) ? $imagenes_juegos[$juego['id']] : 'default.jpg';
                            ?>
                            <div class="game-image">
                                <img src="images/juegos/<?php echo $imagen_juego; ?>" 
                                     alt="<?php echo htmlspecialchars($juego['titulo']); ?>">
                            </div>
                            <div class="game-info">
                                <h3 class="game-title"><?php echo htmlspecialchars($juego['titulo']); ?></h3>
                                <p class="game-genre"><?php echo htmlspecialchars($juego['genero']); ?></p>
                                <p class="game-price">$<?php echo number_format($juego['precio'], 2); ?></p>
                                <p style="color: #ccc; font-size: 14px; margin-top: 8px;">
                                    <?php echo substr(htmlspecialchars($juego['descripcion']), 0, 100) . '...'; ?>
                                </p>
                                <div class="game-buttons">
                                    <a href="games.php?id=<?php echo $juego['id']; ?>" class="btn btn-details">Ver Detalles</a>
                                    <a href="cart.php?action=add&id=<?php echo $juego['id']; ?>" class="btn btn-cart">Añadir Carrito</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #2d2d2d; border-radius: 10px;">
                    <p style="color: #888; font-size: 18px;">No se encontraron juegos para tu búsqueda.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Resultados de Noticias -->
        <section class="news-section">
            <h2 class="section-title">📰 Noticias Encontradas (<?php echo count($resultados_noticias); ?>)</h2>
            
            <?php if (!empty($resultados_noticias)): ?>
                <div class="news-grid">
                    <?php foreach ($resultados_noticias as $noticia): ?>
                        <div class="news-card">
                            <?php 
                            $imagen_noticia = isset($imagenes_noticias[$noticia['id']]) ? $imagenes_noticias[$noticia['id']] : 'default.jpg';
                            ?>
                            <div class="news-image">
                                <img src="images/noticias/<?php echo $imagen_noticia; ?>" 
                                     alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
                            </div>
                            <h3 class="news-title"><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                            <p class="news-content">
                                <?php echo substr(htmlspecialchars($noticia['contenido']), 0, 150) . '...'; ?>
                            </p>
                            <p class="news-date">
                                <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
                                <?php if (isset($noticia['autor'])): ?>
                                    | Por: <?php echo htmlspecialchars($noticia['autor']); ?>
                                <?php endif; ?>
                            </p>
                            <a href="news.php?id=<?php echo $noticia['id']; ?>">Leer más →</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #2d2d2d; border-radius: 10px;">
                    <p style="color: #888; font-size: 18px;">No se encontraron noticias para tu búsqueda.</p>
                </div>
            <?php endif; ?>
        </section>

    <?php else: ?>
        
        <!-- Página inicial de búsqueda -->
        <div style="text-align: center; padding: 60px 20px;">
            <h2 style="color: #ff6b35; margin-bottom: 20px;">¿Qué estás buscando?</h2>
            <p style="color: #ccc; font-size: 18px; margin-bottom: 30px;">
                Busca entre nuestros juegos y las últimas noticias del mundo gaming
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 40px;">
                <div style="background: #2d2d2d; padding: 20px; border-radius: 10px;">
                    <h3 style="color: #ff6b35;">🎮 Buscar Juegos</h3>
                    <p style="color: #ccc;">Encuentra tu próximo juego favorito</p>
                </div>
                <div style="background: #2d2d2d; padding: 20px; border-radius: 10px;">
                    <h3 style="color: #ff6b35;">📰 Buscar Noticias</h3>
                    <p style="color: #ccc;">Mantente al día con las últimas novedades</p>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>