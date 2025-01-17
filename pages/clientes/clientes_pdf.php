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
        // Agregar el logo
        $this->Image('../../assets/logo.jpeg', 10, 6, 30); // Ruta, posición X, posición Y, ancho
        
        // Ajustar el título un poco más arriba
        $this->SetY(25); // Subir el título ligeramente
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('Lista de Clientes'), 0, 1, 'C');
        
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

// Consulta para obtener los clientes
$sql = "SELECT id, nombre, apellido, telefono FROM clientes WHERE deleted = 0";
$result = $conn->query($sql);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(50, 10, 'Nombre', 1);
$pdf->Cell(50, 10, utf8_decode('Apellido'), 1);
$pdf->Cell(50, 10, utf8_decode('Teléfono'), 1);
$pdf->Ln();

// Datos de los clientes
$pdf->SetFont('Arial', '', 10);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 10, $row['id'], 1);
        $pdf->Cell(50, 10, utf8_decode($row['nombre']), 1);
        $pdf->Cell(50, 10, utf8_decode($row['apellido']), 1);
        $pdf->Cell(50, 10, utf8_decode($row['telefono']), 1);
        $pdf->Ln();
    }
} else {
    // Ajustar el ancho de la celda al total de las columnas
    $totalWidth = 20 + 50 + 50 + 50;
    $pdf->Cell($totalWidth, 10, utf8_decode('No se encontraron clientes.'), 1, 1, 'C');
}

// Salida del PDF en el navegador
$pdf->Output('I', 'clientes.pdf');
?>
