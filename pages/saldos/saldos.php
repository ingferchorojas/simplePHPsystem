<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Consulta para obtener los datos necesarios
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
    abonos ab ON ab.numero_documento = ca.numero_documento AND ab.deleted = 0  -- Verificación de que el abono no esté eliminado
INNER JOIN 
    clientes cl ON ca.cliente_id = cl.id
WHERE 
    ca.deleted = 0 AND cl.deleted = 0
GROUP BY 
    ca.id;
";

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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../">Inicio</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Lista de Saldos</h2>
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
                    // Inicializar las variables para sumar los totales
                    $total_no_vencido = 0;
                    $total_de_1_a_15_dias = 0;
                    $total_de_16_a_30_dias = 0;
                    $total_de_31_a_60_dias = 0;
                    $total_mas_de_60_dias = 0;
                    $total_general = 0;

                    while($row = $result->fetch_assoc()):
                        // Sumar los totales
                        $total_no_vencido += $row["no_vencido"];
                        $total_de_1_a_15_dias += $row["de_1_a_15_dias"];
                        $total_de_16_a_30_dias += $row["de_16_a_30_dias"];
                        $total_de_31_a_60_dias += $row["de_31_a_60_dias"];
                        $total_mas_de_60_dias += $row["mas_de_60_dias"];
                        $total_general += $row["total_general"];
                    ?>
                        <tr>
                            <td><?php echo $row["cliente_nombre"]; ?></td>
                            <td><?php echo $row["fecha_documento"]; ?></td>
                            <td><?php echo $row["numero_documento"]; ?></td>
                            <td><?php echo $row["dias_credito"]; ?></td>
                            <td><?php echo $row["dias_vencido"]; ?></td>
                            <td><?php echo number_format($row["no_vencido"], 0, '', '.') . " Gs"; ?></td>
                            <td><?php echo number_format($row["de_1_a_15_dias"], 0, '', '.') . " Gs"; ?></td>
                            <td><?php echo number_format($row["de_16_a_30_dias"], 0, '', '.') . " Gs"; ?></td>
                            <td><?php echo number_format($row["de_31_a_60_dias"], 0, '', '.') . " Gs"; ?></td>
                            <td><?php echo number_format($row["mas_de_60_dias"], 0, '', '.') . " Gs"; ?></td>
                            <td><?php echo number_format($row["total_general"], 0, '', '.') . " Gs"; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <!-- Fila de totales -->
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-center">Totales</th>
                        <th><?php echo number_format($total_no_vencido, 0, '', '.') . " Gs"; ?></th>
                        <th><?php echo number_format($total_de_1_a_15_dias, 0, '', '.') . " Gs"; ?></th>
                        <th><?php echo number_format($total_de_16_a_30_dias, 0, '', '.') . " Gs"; ?></th>
                        <th><?php echo number_format($total_de_31_a_60_dias, 0, '', '.') . " Gs"; ?></th>
                        <th><?php echo number_format($total_mas_de_60_dias, 0, '', '.') . " Gs"; ?></th>
                        <th><?php echo number_format($total_general, 0, '', '.') . " Gs"; ?></th>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>No se encontraron saldos.</p>
        <?php endif; ?>
    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir JS de DataTables -->
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <!-- Inicializar DataTables -->
    <script>
        $(document).ready(function() {
            $('#saldosTable').DataTable();
        });
    </script>
    
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
