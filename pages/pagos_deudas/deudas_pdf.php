<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Incluir la librería FPDF
require('../../libs/fpdf/fpdf.php');

// Obtener los parámetros desde y hasta desde la URL
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// Crear una clase personalizada para el PDF
class PDF extends FPDF
{
    // Cabecera del PDF
    function Header()
    {
        // Agregar el logo
        $this->Image('../../assets/logo.jpeg', 10, 6, 30); // Ruta, posición X, posición Y, ancho
        
        // Ajustar el título un poco más arriba
        $this->SetY(25); // Subir el título ligeramente
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('Lista de Pagos'), 0, 1, 'C');
        
        // Dibujar la línea debajo del título más arriba
        $this->Line(10, 35, 200, 35); // Subir la línea ligeramente
        $this->Ln(10); // Espaciado adicional después de la línea
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

// Mostrar los subtítulos de las fechas "Desde" y "Hasta"
if (!empty($desde) && !empty($hasta)) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, utf8_decode('Desde: ' . $desde), 0, 1, 'L');
    $pdf->Cell(0, 10, utf8_decode('Hasta: ' . $hasta), 0, 1, 'L');
    $pdf->Ln(5); // Espacio adicional después de los subtítulos
}

// Consulta base para obtener las deudas
$sql = "SELECT id, fecha, categoria, notas, monto FROM deudas WHERE 1=1";

// Agregar filtros de fecha si están definidos
if (!empty($desde) && !empty($hasta)) {
    $sql .= " AND fecha BETWEEN ? AND ?";
}

$stmt = $conn->prepare($sql);

if (!empty($desde) && !empty($hasta)) {
    $stmt->bind_param("ss", $desde, $hasta);
}

$stmt->execute();
$result = $stmt->get_result();

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 10, 'Fecha', 1, 0, 'C');
$pdf->Cell(50, 10, utf8_decode('Categoría'), 1, 0, 'C');
$pdf->Cell(70, 10, 'Notas', 1, 0, 'C');
$pdf->Cell(30, 10, 'Monto', 1, 0, 'C');  // Nueva columna Monto
$pdf->Ln();

// Inicializar el total
$totalMonto = 0;
$totalMontoPersonal = 0;
$totalMontoDeudas = 0;
$totalMontoFletes = 0;
$totalMontoPosibles = 0;

// Datos de las deudas
$pdf->SetFont('Arial', '', 10);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(25, 10, utf8_decode($row['fecha']), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode($row['categoria']), 1, 0, 'C');
        $pdf->Cell(70, 10, utf8_decode($row['notas']), 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($row['monto'], 0, '', '.'), 1, 0, 'C');
        $pdf->Ln();

        if ($row['categoria'] == "Pago de personal") {
            $totalMontoPersonal += $row['monto'];
        }
        if ($row['categoria'] == "Pago de deudas") {
            $totalMontoDeudas += $row['monto'];
        }
        if ($row['categoria'] == "Pago de fletes") {
            $totalMontoFletes += $row['monto'];
        }
        if ($row['categoria'] == "Posibles pagos") {
            $totalMontoPosibles += $row['monto'];
        }
        // Sumar el monto al total
        $totalMonto += $row['monto'];
    }
} else {
    // Ajustar el ancho de la celda al total de las columnas
    $totalWidth = 20 + 50 + 50 + 50 + 30;
    $pdf->Cell($totalWidth, 10, utf8_decode('No se encontraron deudas.'), 1, 1, 'C');
}


$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);

// Pago de personal
$pdf->Cell(60, 10, utf8_decode('Pago de personal'), 1, 0, 'C');
$pdf->Cell(60, 10, number_format($totalMontoPersonal, 0, '', '.'), 1, 0, 'C');
$pdf->Ln();

// Pago de deudas
$pdf->Cell(60, 10, utf8_decode('Pago de deudas'), 1, 0, 'C');
$pdf->Cell(60, 10, number_format($totalMontoDeudas, 0, '', '.'), 1, 0, 'C');
$pdf->Ln();

// Pago de fletes
$pdf->Cell(60, 10, utf8_decode('Pago de fletes'), 1, 0, 'C');
$pdf->Cell(60, 10, number_format($totalMontoFletes, 0, '', '.'), 1, 0, 'C');
$pdf->Ln();

// Posibles pagos
$pdf->Cell(60, 10, utf8_decode('Posibles pagos'), 1, 0, 'C');
$pdf->Cell(60, 10, number_format($totalMontoPosibles, 0, '', '.'), 1, 0, 'C');
$pdf->Ln();
// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, utf8_decode('Total'), 1, 0, 'C');
$pdf->Cell(60, 10, number_format($totalMontoPersonal + $totalMontoDeudas + $totalMontoFletes + $totalMontoPosibles, 0, '', '.'), 1, 0, 'C');


// Salida del PDF en el navegador
$pdf->Output('I', 'deudas.pdf');
