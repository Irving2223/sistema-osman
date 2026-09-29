<?php

 include('conexion.php');
 session_start();

$nombre  =   $_POST['nombre'];
$usuario  =   $_POST['usuario'];
$clavee  =   $_POST['clave'];
$pregunta  =   $_POST['pregunta'];
$respuesta  =   $_POST['respuesta'];
$tipo  =   $_POST['tipo'];
$clave = md5($clavee);

$message = '';
$sql1 = "SELECT * FROM usuarios WHERE usuario = '$usuario'"; 
$result1 = mysqli_query($conexion, $sql1);

  if ($result1->num_rows > 0) { 
    $message = "el usuario ya existe."; 
    header("Location: añadir_usuario.php?message=" . urlencode($message));
  } else { 

  	$sql = "INSERT INTO usuarios(nombre,usuario,clave,pregunta,respuesta,tipo)
     values('$nombre','$usuario','$clave','$pregunta','$respuesta','$tipo')";

  $result = mysqli_query($conexion, $sql);

  if ($result) {

  	  $_SESSION['status'] = "Usuario Agregado";
       header("Location: usuarios.php");
	    
	  } else {
	    echo "Error: No se pudo guardar el registro ";
	}

  }
?>