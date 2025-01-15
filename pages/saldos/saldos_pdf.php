<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Incluir la librería FPDF
require('../../libs/fpdf/fpdf.php');

// Crear una clase personalizada para el PDF
class PDF extends FPDF
{
    // Cabecera del PDF
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('Saldos de Clientes'), 0, 1, 'C');
        $this->Line(10, 20, 280, 20); // Línea más ancha para página horizontal
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// Crear instancia del PDF
$pdf = new PDF('L', 'mm', 'A4'); // Orientación horizontal (L) y tamaño A4
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta para obtener los saldos de los clientes
$sql = "
SELECT 
    CONCAT(cl.nombre, ' ', cl.apellido) AS cliente_nombre,
    ca.fecha AS fecha_documento,
    ca.numero_documento,
    ca.dias_credito,
    GREATEST(0, DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY))) AS dias_vencido,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) <= 0 THEN CAST(ca.cargo AS UNSIGNED)
        ELSE 0 
    END AS no_vencido,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) BETWEEN 1 AND 15 THEN CAST(ca.cargo - COALESCE(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS de_1_a_15_dias,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) BETWEEN 16 AND 30 THEN CAST(ca.cargo - COALESCE(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS de_16_a_30_dias,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) BETWEEN 31 AND 60 THEN CAST(ca.cargo - COALESCE(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS de_31_a_60_dias,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) > 60 THEN CAST(ca.cargo - COALESCE(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS mas_de_60_dias,
    CAST(ca.cargo - COALESCE(SUM(ab.monto_abono), 0) AS UNSIGNED) AS total_general
FROM 
    cargos ca
LEFT JOIN 
    abonos ab ON ab.numero_documento = ca.numero_documento AND ab.deleted = 0
INNER JOIN 
    clientes cl ON ca.cliente_id = cl.id
WHERE 
    ca.deleted = 0 AND cl.deleted = 0
GROUP BY 
    ca.id;
";

$result = $conn->query($sql);

// Encabezados de la tabla
// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 10, utf8_decode('Cliente'), 1);
$pdf->Cell(25, 10, utf8_decode('Fecha Doc.'), 1);
$pdf->Cell(20, 10, utf8_decode('N° Doc.'), 1);
$pdf->Cell(18, 10, utf8_decode('Días Cr.'), 1);
$pdf->Cell(18, 10, utf8_decode('Días Venc.'), 1);
$pdf->Cell(25, 10, utf8_decode('No Venc.'), 1);
$pdf->Cell(25, 10, utf8_decode('1 a 15 días'), 1);
$pdf->Cell(25, 10, utf8_decode('16 a 30 días'), 1);
$pdf->Cell(25, 10, utf8_decode('31 a 60 días'), 1);
$pdf->Cell(25, 10, utf8_decode('Más de 60'), 1);
$pdf->Cell(25, 10, utf8_decode('Total Gen.'), 1);
$pdf->Ln();


// Datos de los saldos de los clientes
$pdf->SetFont('Arial', '', 8);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(35, 10, utf8_decode($row['cliente_nombre']), 1);
        $pdf->Cell(25, 10, utf8_decode($row['fecha_documento']), 1);
        $pdf->Cell(20, 10, utf8_decode($row['numero_documento']), 1);
        $pdf->Cell(18, 10, $row['dias_credito'], 1);
        $pdf->Cell(18, 10, $row['dias_vencido'], 1);
        $pdf->Cell(25, 10, number_format($row['no_vencido'], 0, '', '.') . " Gs", 1);
        $pdf->Cell(25, 10, number_format($row['de_1_a_15_dias'], 0, '', '.') . " Gs", 1);
        $pdf->Cell(25, 10, number_format($row['de_16_a_30_dias'], 0, '', '.') . " Gs", 1);
        $pdf->Cell(25, 10, number_format($row['de_31_a_60_dias'], 0, '', '.') . " Gs", 1);
        $pdf->Cell(25, 10, number_format($row['mas_de_60_dias'], 0, '', '.') . " Gs", 1);
        $pdf->Cell(25, 10, number_format($row['total_general'], 0, '', '.') . " Gs", 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, utf8_decode('No se encontraron saldos de clientes.'), 1, 1, 'C');
}


// Salida del PDF en el navegador
$pdf->Output('I', 'saldos_clientes.pdf');
?>
