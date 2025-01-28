<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Variables para las fechas
$fecha_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$fecha_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// Consulta SQL con rango de fechas
$sql = "
SELECT 
    CONCAT(cl.nombre, ' ', cl.apellido) AS cliente_nombre,
    ca.fecha AS fecha_documento,
    ca.numero_documento,
    ca.dias_credito,
    ca.cantidad_cerdos,
    ca.kg,
    GREATEST(0, DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY))) AS dias_vencido,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) <= 0 THEN CAST(ca.cargo AS UNSIGNED)
        ELSE 0 
    END AS no_vencido,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) BETWEEN 1 AND 15 THEN CAST(ca.cargo - IFNULL(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS de_1_a_15_dias,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) BETWEEN 16 AND 30 THEN CAST(ca.cargo - IFNULL(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS de_16_a_30_dias,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) BETWEEN 31 AND 60 THEN CAST(ca.cargo - IFNULL(SUM(ab.monto_abono), 0) AS UNSIGNED)
        ELSE 0 
    END AS de_31_a_60_dias,
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE_ADD(ca.fecha, INTERVAL ca.dias_credito DAY)) > 60 THEN CAST(ca.cargo - IFNULL(SUM(ab.monto_abono), 0) AS UNSIGNED)
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
    ca.deleted = 0 AND cl.deleted = 0";

// Agregar filtro de fechas si se proporcionan
if (!empty($fecha_desde) && !empty($fecha_hasta)) {
    $sql .= " AND ca.fecha BETWEEN '$fecha_desde' AND '$fecha_hasta'";
}

$sql .= " GROUP BY ca.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldos</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluir CSS de DataTables -->
    <link href="../../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- FontAwesome CSS local -->
    <link href="../../assets/fontawesome/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../../assets/logo.png" type="image/png">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../">Inicio</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Lista de Saldos</h2>

        <!-- Formulario para seleccionar rango de fechas -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="desde" class="form-label">Desde:</label>
                    <input type="date" id="desde" name="desde" class="form-control" value="<?= $_GET['desde'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label for="hasta" class="form-label">Hasta:</label>
                    <input type="date" id="hasta" name="hasta" class="form-control" value="<?= $_GET['hasta'] ?? '' ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-secondary ms-2">Limpiar Filtros</a>
                </div>
            </div>
        </form>

        <!-- Botón para imprimir saldos en PDF -->
        <a href="saldos_pdf.php?desde=<?= $_GET['desde'] ?? '' ?>&hasta=<?= $_GET['hasta'] ?? '' ?>" target="_blank" class="btn btn-primary mb-3">Ver PDF</a>

        <?php
        // Calcular el total a cobrar y la deuda pendiente
        $total_a_cobrar = 0;
        $deuda_pendiente = 0;
        $total_kg = 0;
        $total_cerdos = 0;
        $totales_columnas = [
            'no_vencido' => 0,
            'de_1_a_15_dias' => 0,
            'de_16_a_30_dias' => 0,
            'de_31_a_60_dias' => 0,
            'mas_de_60_dias' => 0,
            'total_general' => 0,
        ];

        if (isset($result) && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $total_a_cobrar += $row["total_cargo"];
                $deuda_pendiente += $row["total_general"];
                $total_kg += $row["kg"];
                $total_cerdos += $row["cantidad_cerdos"];
                foreach ($totales_columnas as $col => $total) {
                    $totales_columnas[$col] += $row[$col];
                }
            }
        }
        ?>

        <?php
        // Calcular el efectivo
        $efectivo = $total_a_cobrar - $deuda_pendiente;

        // Consulta SQL con filtro de fechas y suma de montos
        $sql_deudas = "
        SELECT SUM(monto) AS total_pago_deudas
        FROM deudas
        WHERE fecha BETWEEN '$fecha_desde' AND '$fecha_hasta'";

        // Ejecutar la consulta para obtener la suma de pagos de deudas
        $result_deudas = $conn->query($sql_deudas);

        // Comprobar si hay resultados
        if ($result_deudas->num_rows > 0) {
            $row_deudas = $result_deudas->fetch_assoc();
            $pago_deudas = $row_deudas['total_pago_deudas'];
        } else {
            $pago_deudas = 0; // Si no hay resultados, se establece en 0
        }


        ?>

        <!-- Mostrar total a cobrar y deuda pendiente -->
        <div class="container mt-4">
            <div class="row text-center">
                <div class="col-md-3">
                    <h3>Total a Cobrar:</h3>
                    <p class="fw-bold fs-5"><?= number_format($total_a_cobrar, 0, '', '.') ?> Gs</p>
                </div>
                <div class="col-md-3">
                    <h3>Total kg:</h3>
                    <p class="fw-bold fs-5"><?= $total_kg ?> Gs</p>
                </div>
                <div class="col-md-3">
                    <h3>Total cerdos:</h3>
                    <p class="fw-bold fs-5"><?= $total_cerdos ?></p>
                </div>
                <div class="col-md-3">
                    <h3>Cobrado:</h3>
                    <p class="fw-bold fs-5"><?= number_format($efectivo, 0, '', '.') ?> Gs</p>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <h3>No cobrado:</h3>
                    <p class="fw-bold fs-5"><?= number_format($deuda_pendiente, 0, '', '.') ?> Gs</p>
                </div>
                <div class="col-md-4">
                    <h3>Pago de deudas:</h3>
                    <p class="fw-bold fs-5"><?= number_format($pago_deudas, 0, '', '.') ?> Gs</p>
                </div>
                <div class="col-md-4">
                    <h3>Efectivo:</h3>
                    <p class="fw-bold fs-5"><?= number_format($efectivo - $pago_deudas, 0, '', '.') ?> Gs</p>
                </div>
            </div>
        </div>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <table id="saldosTable" class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Nombre del Cliente</th>
                        <th>Fecha Documento</th>
                        <th>Número de Documento</th>
                        <th>Días Crédito</th>
                        <th>Días Vencido</th>
                        <th>No Vencido</th>
                        <th>De 1 a 15 días</th>
                        <th>De 16 a 30 días</th>
                        <th>De 31 a 60 días</th>
                        <th>Más de 60 días</th>
                        <th>Total General</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reiniciar el puntero al resultado para volver a recorrerlo
                    $result->data_seek(0); 
                    while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row["cliente_nombre"] ?></td>
                            <td><?= $row["fecha_documento"] ?></td>
                            <td><?= $row["numero_documento"] ?></td>
                            <td><?= $row["dias_credito"] ?></td>
                            <td><?= $row["dias_vencido"] ?></td>
                            <td><?= number_format($row["no_vencido"], 0, '', '.') . " Gs" ?></td>
                            <td><?= number_format($row["de_1_a_15_dias"], 0, '', '.') . " Gs" ?></td>
                            <td><?= number_format($row["de_16_a_30_dias"], 0, '', '.') . " Gs" ?></td>
                            <td><?= number_format($row["de_31_a_60_dias"], 0, '', '.') . " Gs" ?></td>
                            <td><?= number_format($row["mas_de_60_dias"], 0, '', '.') . " Gs" ?></td>
                            <td><?= number_format($row["total_general"], 0, '', '.') . " Gs" ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <!-- Fila con los totales -->
                    <tr>
                        <td colspan="5"><strong>Total</strong></td>
                        <td><strong><?= number_format($totales_columnas['no_vencido'], 0, '', '.') . " Gs" ?></strong></td>
                        <td><strong><?= number_format($totales_columnas['de_1_a_15_dias'], 0, '', '.') . " Gs" ?></strong></td>
                        <td><strong><?= number_format($totales_columnas['de_16_a_30_dias'], 0, '', '.') . " Gs" ?></strong></td>
                        <td><strong><?= number_format($totales_columnas['de_31_a_60_dias'], 0, '', '.') . " Gs" ?></strong></td>
                        <td><strong><?= number_format($totales_columnas['mas_de_60_dias'], 0, '', '.') . " Gs" ?></strong></td>
                        <td><strong><?= number_format($totales_columnas['total_general'], 0, '', '.') . " Gs" ?></strong></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron saldos en el rango seleccionado.</p>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#saldosTable').DataTable();
        });
    </script>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
