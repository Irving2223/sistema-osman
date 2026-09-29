<?php
session_start();

// Redirigir si no está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'db.php';

// Obtener datos del usuario
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Página Principal</title>
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($user['usuario']); ?></h1>
    <p>ID de usuario: <?php echo $user['id']; ?></p>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>