<?php

include('conexion.php');

$id_materia_prima = $_REQUEST['id_materia_prima'];

$nombre = $_POST['nombre'];
$unidad_medida = $_POST['unidad'];
$descripcion = $_POST['descripcion'];

$sql = "UPDATE materias_primas SET nombre='$nombre',unidad_medida='$unidad_medida',descripcion='$descripcion' WHERE id_materia_prima='$id_materia_prima'";

  $result = mysqli_query($conexion, $sql);

  if ($result) {
	    echo "<script> alert('Perfil Actualizado'); 
              window.location.href = 'materias_primas.php';

	    </script>";
	  

	

	} else {
	    echo "Error: No se pudo guardar el registro ";
	}
