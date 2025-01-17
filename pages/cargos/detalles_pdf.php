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
    // Agregar el logo (ajustá la ruta según corresponda)
    $this->Image('../../assets/logo.jpeg', 10, 6, 30);
    
    // Mover el cursor un poco más abajo antes de colocar el título y la línea
    $this->Ln(15); // Mueve el cursor 15 unidades hacia abajo después del logo
    
    // Configurar el título
    $this->SetFont('Arial', 'B', 12);
    $this->Cell(0, 10, utf8_decode('Detalles del Cargo'), 0, 1, 'C');
    
    // Línea debajo del título
    $this->Line(10, $this->GetY(), 200, $this->GetY()); // Línea debajo del título
    $this->Ln(10); // Mueve más abajo el cursor después de la línea
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
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Obtener el ID del cargo
$id_cargo = $_GET['id'];

// Consulta para obtener los detalles del cargo
$sql = "
    SELECT 
        c.id, 
        c.fecha, 
        cl.nombre, 
        cl.apellido, 
        c.numero_documento, 
        c.dias_credito, 
        c.cargo, 
        c.concepto,
        DATE_ADD(c.fecha, INTERVAL c.dias_credito DAY) AS fecha_vencimiento,
        IF(DATEDIFF(CURRENT_DATE, DATE_ADD(c.fecha, INTERVAL c.dias_credito DAY)) > 0, 'Sí', 'No') AS vencido
    FROM 
        cargos c
    INNER JOIN 
        clientes cl ON c.cliente_id = cl.id
    WHERE 
        c.id = $id_cargo
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $cargo = $result->fetch_assoc();

    // Consulta para obtener los abonos del cargo
    $sql_abonos = "
        SELECT 
            a.nota,
            a.monto_abono, 
            a.fecha 
        FROM 
            abonos a
        WHERE 
            a.numero_documento = '{$cargo['numero_documento']}'
    ";
    $result_abonos = $conn->query($sql_abonos);

    // Calcular el total de abonos
    $total_abonos = 0;
    while ($abono = $result_abonos->fetch_assoc()) {
        $total_abonos += $abono['monto_abono'];
    }

    // Calcular el saldo restante
    $saldo_restante = $cargo['cargo'] - $total_abonos;

    // Reposicionar la consulta para obtener nuevamente los abonos
    $result_abonos->data_seek(0); // Reinicia el puntero del resultado de abonos

    // Encabezado de detalles del cargo
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 10, 'Campo', 1);
    $pdf->Cell(100, 10, 'Detalle', 1);
    $pdf->Ln();

    // Datos del cargo
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 10, 'Nombre:', 1);
    $pdf->Cell(100, 10, utf8_decode($cargo['nombre'] . ' ' . $cargo['apellido']), 1);
    $pdf->Ln();

    $pdf->Cell(50, 10, 'Fecha:', 1);
    $pdf->Cell(100, 10, $cargo['fecha'], 1);
    $pdf->Ln();

    $pdf->Cell(50, 10, 'Documento:', 1);
    $pdf->Cell(100, 10, $cargo['numero_documento'], 1);
    $pdf->Ln();

    $pdf->Cell(50, 10, 'Cargo:', 1);
    $pdf->Cell(100, 10, number_format($cargo['cargo'], 0, '', '.') . ' Gs', 1);
    $pdf->Ln();

    $pdf->Cell(50, 10, 'Fecha Vencimiento:', 1);
    $pdf->Cell(100, 10, $cargo['fecha_vencimiento'], 1);
    $pdf->Ln();

    $pdf->Cell(50, 10, 'Vencido:', 1);
    $pdf->Cell(100, 10, $cargo['vencido'], 1);
    $pdf->Ln();

    $pdf->Cell(50, 10, 'Concepto:', 1);
    $pdf->MultiCell(100, 10, utf8_decode($cargo['concepto']), 1);

    // Mostrar saldo restante
    $pdf->Cell(50, 10, 'Saldo Restante:', 1);
    $pdf->Cell(100, 10, number_format($saldo_restante, 0, '', '.') . ' Gs', 1);
    $pdf->Ln();

    // Encabezado de abonos
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 10, 'Fecha', 1);
    $pdf->Cell(70, 10, 'Nota', 1);
    $pdf->Cell(40, 10, 'Monto Abono', 1);
    $pdf->Ln();

    // Detalles de abonos
    $pdf->SetFont('Arial', '', 10);
    if ($result_abonos->num_rows > 0) {
        while ($abono = $result_abonos->fetch_assoc()) {
            $pdf->Cell(40, 10, $abono['fecha'], 1);
            $pdf->Cell(70, 10, utf8_decode($abono['nota']), 1);
            $pdf->Cell(40, 10, number_format($abono['monto_abono'], 0, '', '.') . ' Gs', 1);
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(150, 10, utf8_decode('No se encontraron abonos.'), 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, utf8_decode('No se encontró el cargo solicitado.'), 1, 1, 'C');
}

// Salida del PDF en el navegador
$pdf->Output('I', 'detalles_cargo.pdf');

?>
