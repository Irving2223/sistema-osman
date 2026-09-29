<?php

include('conexion.php');

$id_usuario = $_POST['id_usuario'];

$clavee = $_POST['clave'];
$clave = md5($clavee);


$sql = "UPDATE usuarios SET clave='$clave' WHERE id_usuario='$id_usuario'";

  $result = mysqli_query($conexion, $sql);

  if ($result) {
	    echo "<script> alert('Contraseña Modificada'); 
              window.location.href = 'index.php';

	    </script>";
	  

	

	} else {
	    echo "Error: No se pudo guardar el registro ";
	}
