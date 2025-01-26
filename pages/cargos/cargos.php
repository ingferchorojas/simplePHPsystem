<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

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
        c.concepto, 
        c.kg,  -- Agregar la columna 'kg'
        c.precio_por_kg,  -- Agregar la columna 'precio_por_kg'
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
    GROUP BY 
        c.id
";


$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargos</title>
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
        <h2>Lista de Cargos</h2>
        
        <!-- Botón para agregar cargo -->
        <a href="agregar_cargo.php" class="btn btn-primary mb-3">Agregar Cargo</a>
        <a href="agregar_abono.php" class="btn btn-primary mb-3">Agregar Abono</a>
        <a href="cargos_pdf.php" target="_blank" class="btn btn-primary mb-3">Ver PDF</a>
        <?php if ($result->num_rows > 0): ?>
            <table id="cargosTable" class="table table-bordered mt-4">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Doc.</th>
                <th>Días de Crédito</th>
                <th>Cargo</th>
                <th>Concepto</th>
                <th>Kg</th> <!-- Nueva columna para kg -->
                <th>Precio por Kg</th> <!-- Nueva columna para precio por kg -->
                <th>Días vencido</th>
                <th>Abonos</th>
                <th>Saldo restante</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row["fecha"]; ?></td>
                <td><?php echo $row["nombre"] . " " . $row["apellido"]; ?></td>
                <td><?php echo $row["numero_documento"]; ?></td>
                <td><?php echo $row["dias_credito"]; ?></td>
                <td><?php echo number_format($row["cargo"], 0, '', '.'); ?> Gs</td>
                <td><?php echo $row["concepto"]; ?></td>
                <td><?php echo $row["kg"]; ?></td> <!-- Mostrar el valor de kg -->
                <td><?php echo number_format($row["precio_por_kg"], 0, '', '.'); ?> Gs</td> <!-- Mostrar el valor de precio por kg -->
                <td><?php echo $row["dias_vencidos"]; ?></td>
                <td><?php echo number_format($row["total_abonos"], 0, '', '.'); ?> Gs</td>
                <td><?php echo number_format($row["cargo"] - $row["total_abonos"], 0, '', '.'); ?> Gs</td>
                <td>
                    <a href="ver_detalles.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
                <td>
                    <a href="editar_cargo.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
                <td>
                    <form action="eliminar_cargo.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cargo?');">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron cargos.</p>
        <?php endif; ?>

    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir JS de DataTables -->
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <!-- Inicializar DataTables -->
    <script>
        $(document).ready(function() {
            $('#cargosTable').DataTable(); // Inicializa DataTables en la tabla con el ID "cargosTable"
        });
    </script>
    
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
