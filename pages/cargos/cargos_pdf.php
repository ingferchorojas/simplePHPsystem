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
        
        // Ajustar el título debajo del logo
        $this->SetY(20); // Posición más abajo
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('Lista de Cargos'), 0, 1, 'C');
        
        // Dibujar la línea debajo del título
        $this->Line(10, 30, 280, 30); // Línea horizontal
        $this->Ln(10); // Espaciado adicional después de la línea
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    // Encabezado de la tabla
function TableHeader()
{
    $this->SetFont('Arial', 'B', 10);
    $this->Cell(20, 10, 'Fecha', 1, 0, 'C');
    $this->Cell(50, 10, utf8_decode('Cliente'), 1, 0, 'C');
    $this->Cell(20, 10, 'Doc', 1, 0, 'C');
    $this->Cell(15, 10, utf8_decode('D. Créd.'), 1, 0, 'C');
    $this->Cell(15, 10, utf8_decode('Kg'), 1, 0, 'C');
    $this->Cell(15, 10, utf8_decode('Cerdos'), 1, 0, 'C');
    $this->Cell(20, 10, 'Cargo', 1, 0, 'C');
    $this->Cell(70, 10, utf8_decode('Concepto'), 1, 0, 'C'); // Nueva columna
    $this->Cell(15, 10, utf8_decode('D Venc.'), 1, 0, 'C');
    $this->Cell(20, 10, 'Abonos', 1, 0, 'C');
    $this->Cell(20, 10, 'Restante', 1, 0, 'C');
    $this->Ln();
}

// Fila de datos
function TableRow($row)
{
    $this->SetFont('Arial', '', 10);
    $this->Cell(20, 10, $row['fecha'], 1, 0, 'C');
    $this->Cell(50, 10, utf8_decode(mb_substr($row['nombre'] . ' ' . $row['apellido'], 0, 19)), 1, 0, 'C');
    $this->Cell(20, 10, $row['numero_documento'], 1, 0, 'C');
    $this->Cell(15, 10, $row['dias_credito'], 1, 0, 'C');
    $this->Cell(15, 10, $row['kg'], 1, 0, 'C');
    $this->Cell(15, 10, $row['cantidad_cerdos'], 1, 0, 'C');
    $this->Cell(20, 10, number_format($row['cargo'], 0, '', '.'), 1, 0, 'C');
    $this->Cell(70, 10, utf8_decode(mb_substr($row['concepto'], 0, 29)), 1, 0, 'C'); // Nueva celda para el concepto
    $this->Cell(15, 10, $row['dias_vencidos'], 1, 0, 'C');
    $this->Cell(20, 10, number_format($row['total_abonos'], 0, '', '.'), 1, 0, 'C');
    $this->Cell(20, 10, number_format($row['cargo'] - $row['total_abonos'], 0, '', '.'), 1, 0, 'C');
    $this->Ln();
}

}

// Crear instancia del PDF
$pdf = new PDF();
// Cambiar a tamaño A3 o personalizar dimensiones
$pdf->AddPage('L', 'A4'); // 'L' para orientación horizontal, 'A3' para tamaño más grande
$pdf->SetFont('Arial', '', 10);


// Consulta para obtener los cargos
$sql = "
    SELECT 
        c.id, 
        c.fecha, 
        c.cliente_id, 
        cl.nombre, 
        cl.apellido, 
        c.numero_documento, 
        c.dias_credito, 
        c.cargo, 
        c.kg,
        c.cantidad_cerdos,
        c.concepto, 
        SUM(a.monto_abono) AS total_abonos,
        IF(DATEDIFF(CURRENT_DATE, DATE_ADD(c.fecha, INTERVAL c.dias_credito DAY)) < 0, 0, DATEDIFF(CURRENT_DATE, DATE_ADD(c.fecha, INTERVAL c.dias_credito DAY))) AS dias_vencidos
    FROM 
        cargos c
    INNER JOIN 
        clientes cl ON c.cliente_id = cl.id
    LEFT JOIN 
        abonos a ON a.numero_documento = c.numero_documento
    WHERE 
        c.deleted = 0
        and cl.deleted = 0
    GROUP BY 
        c.id
";

$result = $conn->query($sql);

// Encabezado de la tabla
$pdf->TableHeader();

// Datos de los cargos
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->TableRow($row);
    }
} else {
    $pdf->Cell(0, 10, utf8_decode('No se encontraron cargos.'), 1, 1, 'C');
}

// Salida del PDF en el navegador
$pdf->Output('I', 'cargos.pdf');
?>
