<?php
session_start();

// Destruir la sesión
session_destroy();

// Redirigir
header('Location: index.php');
exit;
?>