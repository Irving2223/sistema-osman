<?php

include('conexion.php');

$id_proveedor = $_REQUEST['id_proveedor'];

$nombre = $_POST['nombre'];
$rif = $_POST['rif'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$sql = "UPDATE proveedores SET nombre='$nombre',rif='$rif',correo='$correo',telefono='$telefono' WHERE id_proveedor='$id_proveedor'";

  $result = mysqli_query($conexion, $sql);

  if ($result) {
	    echo "<script> alert('Perfil Actualizado'); 
              window.location.href = 'proveedores.php';

	    </script>";
	  

	

	} else {
	    echo "Error: No se pudo guardar el registro ";
	}
