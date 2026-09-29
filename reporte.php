<?php


include("conexion.php");
require('fpdf/fpdf.php');

$inicio =   $_POST['inicio'];
$fin  =   $_POST['fin'];

$sql = "SELECT a.id_asist,DATE_FORMAT(a.fecha, '%d-%m-%Y') AS fecha,a.hora_entrada as entrada,a.hora_salida as salida,d.nombre AS docente,s.id_grado AS grado, s.seccion AS seccion, a.actividad as actividad,a.hembras as hembras,a.varones as varones,a.total as total FROM asistencia a JOIN docente d ON a.id_docente = d.id_docente JOIN secciones s ON a.id_seccion = s.id_seccion WHERE fecha BETWEEN '$inicio' AND '$fin' ";
$result = mysqli_query($conexion, $sql);


// Crear instancia de FPDF
$pdf = new FPDF('L', 'mm', 'A4'); // 'L' para horizontal
$pdf->AddPage();

$pdf->Image('assets/img/cbit.jpg', 10, 10, 40); // Ajusta la ruta y el tamaño
$pdf->Ln(10);
// Membrete
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 20, 'Listado de Asistencias', 0, 1, 'C'); // Centrado

// Salto de línea
$pdf->Ln(10);

// Establecer fuente
$pdf->SetFont('Arial', 'B', 12);

// Encabezados de la tabla
$pdf->Cell(13, 10, 'No', 1);
$pdf->Cell(25, 10, 'Fecha', 1);
$pdf->Cell(20, 10, 'Entrada', 1);
$pdf->Cell(20, 10, 'Salida', 1);
$pdf->Cell(40, 10, 'Docente', 1);
$pdf->Cell(17, 10, 'Grado', 1);
$pdf->Cell(20, 10, 'Seccion', 1);
$pdf->Cell(70, 10, 'Actividad', 1);
$pdf->Cell(13, 10, 'H', 1);
$pdf->Cell(13, 10, 'V', 1);
$pdf->Cell(13, 10, 'T', 1);

$pdf->Ln();

// Contenido de la tabla
$pdf->SetFont('Arial', '', 12);
$contador = 0;
foreach ($result as $fila) {
	$pdf->Cell(13, 10, $contador+=1, 1);
    $pdf->Cell(25, 10, $fila['fecha'], 1);
	$pdf->Cell(20, 10, $fila['entrada'], 1);
	$pdf->Cell(20, 10, $fila['salida'], 1);
	$pdf->Cell(40, 10, $fila['docente'], 1);
	$pdf->Cell(17, 10, $fila['grado'], 1);
	$pdf->Cell(20, 10, $fila['seccion'], 1);
	$pdf->Cell(70, 10, $fila['actividad'], 1);
	$pdf->Cell(13, 10, $fila['hembras'], 1);
	$pdf->Cell(13, 10, $fila['varones'], 1);
	$pdf->Cell(13, 10, $fila['total'], 1);
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output(); // 'D' para descargar el archivo
?>