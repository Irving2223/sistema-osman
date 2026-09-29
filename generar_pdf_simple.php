<?php
include("conexion.php");
require('fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,'REPORTE DE INVENTARIO',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Generado el: ' . date('d/m/Y H:i:s'),0,1,'R');
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Obtener datos
$sql = "SELECT mp.nombre, mp.unidad_medida, COALESCE(i.cantidad_actual,0) as stock 
        FROM materias_primas mp 
        LEFT JOIN inventario i ON mp.id_materia_prima = i.id_materia_prima 
        ORDER BY mp.nombre";
$result = mysqli_query($conexion, $sql);

// Encabezados de tabla
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,'Materia Prima',1,0,'C');
$pdf->Cell(40,10,'Unidad',1,0,'C');
$pdf->Cell(40,10,'Stock',1,1,'C');

// Datos
$pdf->SetFont('Arial','',10);
while($row = $result->fetch_assoc()) {
    $pdf->Cell(80,8,$row['nombre'],1,0,'L');
    $pdf->Cell(40,8,$row['unidad_medida'],1,0,'C');
    $pdf->Cell(40,8,number_format($row['stock'],2),1,1,'R');
}

$pdf->Output('I','inventario.pdf');
?>