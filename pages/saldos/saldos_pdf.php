<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Incluir la librería FPDF
require('../../libs/fpdf/fpdf.php');

// Recibir parámetros desde y hasta
$desde = isset($_GET['desde']) ? $_GET['desde'] : null;
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : null;

// Validar formato de fecha
function validarFecha($fecha) {
    $formato = 'Y-m-d';
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

if ($desde && !validarFecha($desde)) {
    die("Fecha 'desde' inválida.");
}
if ($hasta && !validarFecha($hasta)) {
    die("Fecha 'hasta' inválida.");
}

// Crear una clase personalizada para el PDF
class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../assets/logo.jpeg', 10, 8, 33);
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(20);
        $this->Cell(0, 10, utf8_decode('Saldos de Clientes'), 0, 1, 'C');
        $this->Line(10, 40, 280, 40);
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// Crear instancia del PDF
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Construir la consulta SQL
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
    CAST(ca.cargo AS UNSIGNED) AS total_cargo,
    CAST(ca.cargo - IFNULL(SUM(ab.monto_abono), 0) AS UNSIGNED) AS total_general
FROM 
    cargos ca
LEFT JOIN 
    abonos ab ON ab.numero_documento = ca.numero_documento AND ab.deleted = 0
INNER JOIN 
    clientes cl ON ca.cliente_id = cl.id
WHERE 
    ca.deleted = 0 AND cl.deleted = 0
";

if ($desde) {
    $sql .= " AND ca.fecha >= '$desde'";
}
if ($hasta) {
    $sql .= " AND ca.fecha <= '$hasta'";
}

$sql .= " GROUP BY ca.id";

// Ejecutar consulta
$result = $conn->query($sql);

// Variables para mostrar los totales
$total_a_cobrar = 0;
$deuda_pendiente = 0;

// Calculamos los totales
if (isset($result) && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $total_a_cobrar += $row["total_cargo"];
        $deuda_pendiente += $row["total_general"];
    }
}

$efectivo = $total_a_cobrar - $deuda_pendiente;


// Mostrar los totales sobre la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 10, utf8_decode('Total a cobrar: ' . number_format($total_a_cobrar, 0, '', '.') . ' Gs.'), 0, 0, 'L');
$pdf->Cell(50, 10, utf8_decode('Deuda pendiente: ' . number_format($deuda_pendiente, 0, '', '.') . ' Gs.'), 0, 1, 'L');
$pdf->Cell(50, 10, utf8_decode('Deuda pendiente: ' . number_format($total_a_cobrar - $deuda_pendiente, 0, '', '.') . ' Gs.'), 0, 1, 'L');

$pdf->Ln(5);

// Encabezados de la tabla
$pdf->Cell(50, 10, utf8_decode('Cliente'), 1, 0, 'C');
$pdf->Cell(20, 10, utf8_decode('Fecha'), 1, 0, 'C');
$pdf->Cell(25, 10, utf8_decode('Doc.'), 1, 0, 'C');
$pdf->Cell(20, 10, utf8_decode('Días Créd.'), 1, 0, 'C');
$pdf->Cell(20, 10, utf8_decode('Días Venc.'), 1, 0, 'C');
$pdf->Cell(20, 10, utf8_decode('No Venc.'), 1, 0, 'C');
$pdf->Cell(25, 10, utf8_decode('1 a 15 días'), 1, 0, 'C');
$pdf->Cell(25, 10, utf8_decode('16 a 30 días'), 1, 0, 'C');
$pdf->Cell(25, 10, utf8_decode('31 a 60 días'), 1, 0, 'C');
$pdf->Cell(25, 10, utf8_decode('Más de 60'), 1, 0, 'C');
$pdf->Cell(25, 10, utf8_decode('Total'), 1, 0, 'C');
$pdf->Ln();

// Datos de los saldos de los clientes
$pdf->SetFont('Arial', '', 10);
if ($result->num_rows > 0) {
    $result->data_seek(0); // Volver al inicio del resultado
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(50, 10, utf8_decode($row['cliente_nombre']), 1, 0, 'C');
        $pdf->Cell(20, 10, utf8_decode($row['fecha_documento']), 1, 0, 'C');
        $pdf->Cell(25, 10, utf8_decode($row['numero_documento']), 1, 0, 'C');
        $pdf->Cell(20, 10, $row['dias_credito'], 1, 0, 'C');
        $pdf->Cell(20, 10, $row['dias_vencido'], 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($row['no_vencido'], 0, '', '.'), 1, 0, 'C');
        $pdf->Cell(25, 10, number_format($row['de_1_a_15_dias'], 0, '', '.'), 1, 0, 'C');
        $pdf->Cell(25, 10, number_format($row['de_16_a_30_dias'], 0, '', '.'), 1, 0, 'C');
        $pdf->Cell(25, 10, number_format($row['de_31_a_60_dias'], 0, '', '.'), 1, 0, 'C');
        $pdf->Cell(25, 10, number_format($row['mas_de_60_dias'], 0, '', '.'), 1, 0, 'C');
        $pdf->Cell(25, 10, number_format($row['total_general'], 0, '', '.'), 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(280, 10, utf8_decode('No se encontraron saldos de clientes.'), 1, 1, 'C');
}

// Salida del PDF en el navegador
$pdf->Output('I', 'saldos_clientes.pdf');
?>
