<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Obtener el ID del cargo
$id_cargo = $_GET['id'];

// Consulta para obtener los detalles del cargo
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

// Ejecutar la consulta
$result = $conn->query($sql);

// Verificar si se obtuvo un resultado
if ($result->num_rows > 0) {
    $cargo = $result->fetch_assoc();

    // Consulta para obtener los abonos del cargo
    $sql_abonos = "
        SELECT 
            a.id,
            a.nota,
            a.monto_abono, 
            a.fecha 
        FROM 
            abonos a
        WHERE 
            a.numero_documento = '{$cargo['numero_documento']}'
    ";

    $result_abonos = $conn->query($sql_abonos);

    // Calcular el total abonado
    $total_abonado = 0;
    while ($abono = $result_abonos->fetch_assoc()) {
        $total_abonado += $abono['monto_abono'];
    }
    // Volver al inicio de la consulta de abonos
    $result_abonos->data_seek(0); 
} else {
    echo "No se encontró el cargo.";
    exit;
}
// Calcular el total restante
$total_restante = $cargo['cargo'] - $total_abonado;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Cargo</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .details-box {
            padding: 15px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Detalles del Cargo</h2>
        <a href="detalles_pdf.php?id=<?php echo $id_cargo; ?>" target="_blank" class="btn btn-primary mb-3">Ver PDF</a>
        <!-- Información del Cargo -->
        <div class="details-box">
            <h4>Información del Cargo</h4>
            <p><strong>Nombre:</strong> <?php echo $cargo['nombre'] . " " . $cargo['apellido']; ?></p>
            <p><strong>Fecha:</strong> <?php echo $cargo['fecha']; ?></p>
            <p><strong>Documento:</strong> <?php echo $cargo['numero_documento']; ?></p>
            <p><strong>Días Crédito:</strong> <?php echo $cargo['dias_credito']; ?></p>
            <p><strong>Cargo:</strong> <?php echo number_format($cargo['cargo'], 0, '', '.'); ?> Gs</p>

            <!-- Total abonado -->
            <p><strong>Total Abonado:</strong> <?php echo number_format($total_abonado, 0, '', '.'); ?> Gs</p>

            <!-- Total restante -->
            <p><strong>Total Restante:</strong> <?php echo number_format($total_restante, 0, '', '.'); ?> Gs</p>
            
            <p><strong>Concepto:</strong> <?php echo $cargo['concepto']; ?></p>
            <p><strong>Fecha de Vencimiento:</strong> <?php echo $cargo['fecha_vencimiento']; ?></p>
            <p><strong>Vencido:</strong> <?php echo $cargo['vencido']; ?></p>
        </div>

        <!-- Tabla de Abonos -->
        <div class="details-box">
            <h4>Abonos</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Monto Abono</th>
                        <th>Nota</th>
                        <th>Fecha</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($abono = $result_abonos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo number_format($abono['monto_abono'], 0, '', '.'); ?> Gs</td>
                            <td><?php echo $abono['nota']; ?></td>
                            <td><?php echo $abono['fecha']; ?></td>
                            <td>
                            <form action="eliminar_abono.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este abono?');">
                                <input type="hidden" name="id" value="<?php echo $abono['id']; ?>">
                                <input type="hidden" name="cargo_id" value="<?php echo $cargo['id']; ?>"> <!-- Corrección aquí -->
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="cargos.php" class="btn btn-secondary">Volver a la lista de cargos</a>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
