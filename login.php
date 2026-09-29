<?php


require_once('conexion.php');

session_start();

$user = $_POST["usuario"];
$clavee= $_POST["clave"];
$clave = md5($clavee);



	
    
    $sql= "SELECT usuario FROM usuarios where usuario = '$user'";
    $sql2= "SELECT usuario FROM usuarios where clave = '$clave'";
    $result = mysqli_query($conexion,$sql);
    $resul= mysqli_query($conexion,$sql2);
    $usu = mysqli_num_rows($result);
    $cla = mysqli_num_rows($resul);
    $row=mysqli_fetch_assoc($result);
   
    

  

    if ($usu == 0) {

    	$_SESSION['error'] = "Usuario no existe";
       header("Location: index.php");
    }else if ($cla == 0) {

    	 
    $_SESSION['status'] = "Contraseña Incorrecta";
       header("Location: index.php");
         
    
    }
    

    else{

    	$sql3= "SELECT id_usuario,nombre,usuario,clave,tipo FROM usuarios WHERE usuario = '$user' AND clave= '$clave'";
	    $resu = mysqli_query($conexion,$sql3);
        $fila=mysqli_fetch_assoc($resu);
		$_SESSION["user"] =$fila['usuario'];
		$_SESSION["id_usuario"] =$fila['id_usuario'];
		$_SESSION["nombre"] =$fila['nombre'];
		$_SESSION["tipo"] =$fila['tipo'];

		

		
		
		header("Location: inicio.php");


    }





   





	




  

   
    



      




/*
    
   	if (mysqli_num_rows($resultado) == 0)
	{
		echo "<script text='text/javascript'>
		alert('Usuario y/o clave incorrecta');
		window.location = 'index.php';
		</script>";
	}else
	{
	
	    $row=mysqli_fetch_assoc($resultado);
		$_SESSION["user"] =$row['usuario'];
		$_SESSION["tipo"] =$row['tipo'];
		
		header("Location: inicio.php");
	}
	


?>

*/