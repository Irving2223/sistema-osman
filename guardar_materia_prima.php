<?php

 include('conexion.php');
 session_start();

$nombre  =   $_POST['nombre'];
$unidad_medida  =   $_POST['unidad'];
$descripcion  =   $_POST['descripcion'];

  	$sql = "INSERT INTO materias_primas(nombre,unidad_medida,descripcion)
     values('$nombre','$unidad_medida','$descripcion')";

  $result = mysqli_query($conexion, $sql);

  if ($result) {

  	  $_SESSION['status'] = "Materia Prima Agregado";
       header("Location: materias_primas.php");
	    
	  } else {
	    echo "Error: No se pudo guardar la materia prima ";
	}

?>