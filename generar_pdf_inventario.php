<?php
include("conexion.php");

// Cargar TCPDF
require_once('tcpdf/tcpdf.php');

// Obtener datos del inventario
$sql = "
    SELECT 
        mp.id_materia_prima,
        mp.nombre,
        mp.unidad_medida,
        COALESCE(i.cantidad_actual, 0) as stock_actual,
        i.fecha_actualizacion
    FROM materias_primas mp
    LEFT JOIN inventario i ON mp.id_materia_prima = i.id_materia_prima
    ORDER BY mp.nombre
";

$result = mysqli_query($conexion, $sql);
$materias_primas = [];
$total_stock = 0;
$con_stock_bajo = 0;

while ($row = $result->fetch_assoc()) {
    $materias_primas[] = $row;
    $total_stock += $row['stock_actual'];
    if ($row['stock_actual'] < 10) {
        $con_stock_bajo++;
    }
}

// Crear nuevo documento PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema de Inventario');
$pdf->SetAuthor('Sistema de Inventario');
$pdf->SetTitle('Reporte de Inventario');
$pdf->SetSubject('Inventario de Materias Primas');

// Margenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Salto de página automático
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar página
$pdf->AddPage();

// Logo (opcional)
// $pdf->Image('logo.png', 15, 15, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE INVENTARIO', 0, 1, 'C');
$pdf->Ln(5);

// Fecha de generación
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
$pdf->Ln(5);

// Estadísticas rápidas
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'RESUMEN GENERAL', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Crear tabla de resumen
$resumen = '
<table border="1" cellpadding="4" style="border-collapse: collapse;">
    <tr style="background-color: #f8f9fa;">
        <td width="25%"><b>Total Materias Primas:</b></td>
        <td width="25%">' . count($materias_primas) . '</td>
        <td width="25%"><b>Con Stock Bajo:</b></td>
        <td width="25%">' . $con_stock_bajo . '</td>
    </tr>
    <tr>
        <td><b>Stock Total:</b></td>
        <td>' . number_format($total_stock, 2) . '</td>
        <td><b>Fecha Reporte:</b></td>
        <td>' . date('d/m/Y') . '</td>
    </tr>
</table>
';

$pdf->writeHTML($resumen, true, false, true, false, '');
$pdf->Ln(10);

// Tabla de inventario
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'DETALLE DE MATERIAS PRIMAS', 0, 1, 'L');
$pdf->Ln(2);

// Crear tabla HTML para el PDF
$html = '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    th {
        background-color: #343a40;
        color: white;
        font-weight: bold;
        padding: 6px;
        border: 1px solid #ddd;
    }
    td {
        padding: 5px;
        border: 1px solid #ddd;
    }
    .stock-bajo {
        background-color: #fff3cd;
        color: #856404;
    }
    .sin-stock {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<table>
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="45%">MATERIA PRIMA</th>
            <th width="15%">UNIDAD</th>
            <th width="20%">STOCK ACTUAL</th>
            <th width="15%">ÚLTIMA ACT.</th>
        </tr>
    </thead>
    <tbody>
';

$contador = 1;
foreach ($materias_primas as $materia) {
    $clase = '';
    if ($materia['stock_actual'] <= 0) {
        $clase = 'sin-stock';
    } elseif ($materia['stock_actual'] < 10) {
        $clase = 'stock-bajo';
    }
    
    $html .= '
        <tr class="' . $clase . '">
            <td width="5%">' . $contador . '</td>
            <td width="45%">' . htmlspecialchars($materia['nombre']) . '</td>
            <td width="15%">' . htmlspecialchars($materia['unidad_medida']) . '</td>
            <td width="20%">' . number_format($materia['stock_actual'], 2) . '</td>
            <td td width="15%">' . ($materia['fecha_actualizacion'] ? date('d/m/Y', strtotime($materia['fecha_actualizacion'])) : 'Nunca') . '</td>
        </tr>
    ';
    $contador++;
}

$html .= '
    </tbody>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Pie de página con totales
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 8, 'TOTAL DE MATERIALES: ' . count($materias_primas), 0, 1, 'L');
$pdf->Cell(0, 8, 'MATERIALES CON STOCK BAJO: ' . $con_stock_bajo, 0, 1, 'L');
$pdf->Cell(0, 8, 'MATERIALES SIN STOCK: ' . count(array_filter($materias_primas, function($m) { return $m['stock_actual'] <= 0; })), 0, 1, 'L');

// Salida del PDF
$pdf->Output('inventario_' . date('Y-m-d') . '.pdf', 'I');

// Cerrar conexión
$conexion->close();
?>