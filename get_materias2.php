<?php
// get_materias.php
include("conexion.php");
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Important para CORS

$sql = "SELECT id_materia_prima, nombre FROM materias_primas";
$result = mysqli_query($conexion, $sql);

$materias = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $materias[] = $row;
    }
}

echo json_encode($materias);
mysqli_close($conexion);
?>