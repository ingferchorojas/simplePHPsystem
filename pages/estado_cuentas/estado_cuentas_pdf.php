<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Incluir la librería FPDF para la creación del PDF
require('../../libs/fpdf/fpdf.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Obtener datos del formulario
    $cliente_id = $_GET['cliente_id']; // ID del cliente seleccionado
    $fecha_desde = $_GET['fecha_desde']; // Fecha desde
    $fecha_hasta = $_GET['fecha_hasta']; // Fecha hasta

    // Consulta para establecer las variables
    $sql_set = "
        SET @cliente_id = '$cliente_id';
        SET @fecha_desde = '$fecha_desde';
        SET @fecha_hasta = '$fecha_hasta';
    ";

    // Ejecutar la consulta para establecer las variables
    if (!$conn->multi_query($sql_set)) {
        die("Error al establecer variables: " . $conn->error);
    }

    // Esperar a que la ejecución de multi_query termine antes de hacer la consulta principal
    do {
        // Si la ejecución de multi_query tiene resultados, los descarta
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());

    // Consulta principal para obtener los cargos y abonos
    $sql = "
        -- Cargar los cargos
        SELECT 
            cl.nombre AS cliente_nombre,
            cl.apellido AS cliente_apellido,
            ca.fecha AS fecha,
            'Cargo' AS concepto,
            ca.numero_documento AS documento,
            ca.cargo AS cargo,
            0 AS abono,
            ca.cargo AS subtotal
        FROM 
            clientes cl
        JOIN 
            cargos ca ON cl.id = ca.cliente_id 
        WHERE 
            cl.id = @cliente_id
            AND ca.deleted = 0
            AND ca.fecha BETWEEN @fecha_desde AND @fecha_hasta

        UNION ALL

        -- Cargar los abonos, pero solo si existe un cargo previo
        SELECT 
            cl.nombre AS cliente_nombre,
            cl.apellido AS cliente_apellido,
            ab.fecha AS fecha,
            'Abono' AS concepto,
            ab.numero_documento AS documento,
            0 AS cargo,
            ab.monto_abono AS abono,
            ab.monto_abono AS subtotal
        FROM 
            clientes cl
        JOIN 
            abonos ab ON cl.id = ab.cliente_id 
        WHERE 
            cl.id = @cliente_id
            AND ab.deleted = 0
            AND ab.fecha BETWEEN @fecha_desde AND @fecha_hasta
            AND EXISTS (
                SELECT 1
                FROM cargos ca 
                WHERE ca.cliente_id = cl.id 
                AND ca.fecha <= ab.fecha
                AND ca.deleted = 0
            )

        ORDER BY 
            fecha;
    ";

    // Ejecutar la consulta principal y obtener los resultados
    $result = $conn->query($sql);
    $movimientos = [];
    $total_cargos = 0;
    $total_abonos = 0;

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
            // Sumar los cargos y abonos
            $total_cargos += $row['cargo'];
            $total_abonos += $row['abono'];
        }
    }
    // Calcular el total adeudado
    $total_adeudado = $total_cargos - $total_abonos;
}

// Crear el objeto PDF
$pdf = new FPDF();
$pdf->AddPage();

// Establecer título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Estado de Cuentas', 0, 1, 'C');
$pdf->Ln(10);

// Establecer encabezado de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 10, 'Fecha', 1, 0, 'C');
$pdf->Cell(30, 10, 'Concepto', 1, 0, 'C');
$pdf->Cell(40, 10, 'Numero Documento', 1, 0, 'C');
$pdf->Cell(25, 10, 'Cargo', 1, 0, 'C');
$pdf->Cell(25, 10, 'Abono', 1, 0, 'C');
$pdf->Cell(30, 10, 'Subtotal', 1, 1, 'C');

// Llenar la tabla con los datos
$pdf->SetFont('Arial', '', 10);
foreach ($movimientos as $movimiento) {
    $pdf->Cell(30, 10, utf8_decode($movimiento['fecha']), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($movimiento['concepto']), 1, 0, 'C');
    $pdf->Cell(40, 10, utf8_decode($movimiento['documento']), 1, 0, 'C');
    $pdf->Cell(25, 10, number_format($movimiento['cargo'], 0, '', '.'), 1, 0, 'C');
    $pdf->Cell(25, 10, number_format($movimiento['abono'], 0, '', '.'), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($movimiento['subtotal'], 0, '', '.'), 1, 1, 'C');
}

// Mostrar el total adeudado
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Total Adeudado:', 1, 0, 'R');
$pdf->Cell(30, 10, number_format($total_adeudado, 0, '', '.'), 1, 1, 'C');

// Salida del PDF
$pdf->Output();
?>
