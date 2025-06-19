<?php
// index.php - Página principal vulnerable a SQLi
include 'includes/db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusPlay - Gaming Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="navbar">
            <h1><a href="index.php">🎮 NexusPlay</a></h1>
            <div class="nav-links">
                <?php if(isset($_SESSION['username'])): ?>
                    <span>Bienvenido, <?php echo $_SESSION['username']; ?>!</span>
                    <a href="logout.php">Salir</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <div class="search-section">
            <h2>Buscar Juegos</h2>
            <form method="GET" action="index.php">
                <input type="text" name="titulo" placeholder="Buscar por título..." 
                       value="<?php echo isset($_GET['titulo']) ? htmlspecialchars($_GET['titulo']) : ''; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <div class="games-section">
            <h2>Lista de Juegos</h2>
            
            <?php
            // VULNERABILIDAD SQLi INTENCIONAL
            if(isset($_GET['titulo']) && !empty($_GET['titulo'])) {
                $titulo = $_GET['titulo']; // SIN SANITIZACIÓN
                
                // Consulta vulnerable a SQLi
                $sql = "SELECT * FROM games WHERE title LIKE '%$titulo%'";
                echo "<p><small>Debug SQL: $sql</small></p>";
                
                $games = executeQuery($sql);
            } else {
                // Mostrar todos los juegos por defecto
                $sql = "SELECT * FROM games LIMIT 10";
                $games = executeQuery($sql);
            }
            
            if($games && count($games) > 0): ?>
                <div class="games-grid">
                    <?php foreach($games as $game): ?>
                        <div class="game-card">
                            <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                            <p><strong>Género:</strong> <?php echo htmlspecialchars($game['genre']); ?></p>
                            <p><strong>Precio:</strong> $<?php echo htmlspecialchars($game['price']); ?></p>
                            <p><?php echo htmlspecialchars(substr($game['description'], 0, 100)); ?>...</p>
                            <a href="game.php?id=<?php echo $game['id']; ?>" class="btn">Ver detalles</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No se encontraron juegos.</p>
            <?php endif; ?>
        </div>

       
    </main>
</body>
</html>