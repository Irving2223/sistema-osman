<?php
// Conexion a la base de datos.
// Los datos de acceso se leen del archivo .env (ver config.php)
require_once('config.php');

$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Error al conectar " . mysqli_connect_error();
  }

?>
