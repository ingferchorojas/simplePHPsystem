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
            ca.cargo AS subtotal,
            ca.concepto AS nota -- Aquí agregamos la columna 'concepto' para los cargos
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
            ab.monto_abono AS subtotal,
            ab.nota AS nota -- Aquí agregamos la columna 'nota' para los abonos
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
$pdf->AddPage('L'); // Cambiar a orientación horizontal (landscape)

// Incluir el logo en la parte superior
$pdf->Image('../../assets/logo.jpeg', 10, 10, 30); // Ajusta la ruta y el tamaño (10, 10, 30) si es necesario

// Establecer título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Ln(20); // Reducir el espacio después del logo
$pdf->Cell(0, 10, 'Estado de Cuentas', 0, 1, 'C');
$pdf->Ln(5); // Reducir el espacio antes de la tabla

// Establecer encabezado de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'Fecha', 1, 0, 'C');
$pdf->Cell(20, 10, 'Concepto', 1, 0, 'C');
$pdf->Cell(40, 10, utf8_decode('N° Documento'), 1, 0, 'C');
$pdf->Cell(25, 10, 'Cargo', 1, 0, 'C');
$pdf->Cell(25, 10, 'Abono', 1, 0, 'C');
$pdf->Cell(30, 10, 'Subtotal', 1, 0, 'C');
$pdf->Cell(110, 10, 'Notas', 1, 1, 'C'); // Hacer la columna "Notas" más ancha

// Llenar la tabla con los datos
$pdf->SetFont('Arial', '', 10);
foreach ($movimientos as $movimiento) {
    $nota = utf8_decode($movimiento['concepto'] == 'Cargo' ? $movimiento['nota'] : $movimiento['nota']);
    $pdf->Cell(20, 10, utf8_decode($movimiento['fecha']), 1, 0, 'C');
    $pdf->Cell(20, 10, utf8_decode($movimiento['concepto']), 1, 0, 'C');
    $pdf->Cell(40, 10, utf8_decode($movimiento['documento']), 1, 0, 'C');
    $pdf->Cell(25, 10, number_format($movimiento['cargo'], 0, '', '.'), 1, 0, 'C');
    $pdf->Cell(25, 10, number_format($movimiento['abono'], 0, '', '.'), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($movimiento['subtotal'], 0, '', '.'), 1, 0, 'C');
    
    // Usar Cell en lugar de MultiCell para la columna de Notas
    $pdf->Cell(110, 10, $nota, 1, 0, 'C'); // La columna de Notas con un solo Cell

    // Asegurarse de que la próxima fila esté en la misma línea
    $pdf->Ln();
}

// Mostrar el total adeudado con el mismo ancho de la tabla
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$total_width = 30 + 30 + 40 + 25 + 25 + 30 + 40; // Ancho total de las columnas de la tabla (sin contar las celdas de "Notas")
$pdf->Cell($total_width, 10, 'Total Adeudado:', 1, 0, 'R'); // Ancho de la celda igual al ancho total de la tabla
$pdf->Cell(50, 10, number_format($total_adeudado, 0, '', '.'), 1, 1, 'C'); // La columna de Total Adeudado ocupa el espacio restante

// Mostrar el número de página
$pdf->AliasNbPages();
$pdf->SetFont('Arial', 'I', 8);
$pdf->Ln(5); 
$pdf->Cell(0, 10, utf8_decode('Página ') . $pdf->PageNo() . ' de {nb}', 0, 0, 'C');

// Salida del PDF
$pdf->Output();
?>
