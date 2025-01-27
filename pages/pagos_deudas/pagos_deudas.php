<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Inicializar variables para el rango de fechas
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// Consulta base para obtener las deudas
$sql = "SELECT id, fecha, categoria, notas, monto FROM deudas WHERE 1=1";  // Se agregó "monto"

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos</title>
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
        <h2>Lista de Pagos</h2>
        
        <!-- Botón para agregar deuda -->
        <a href="agregar_deuda.php" class="btn btn-primary mb-3">Agregar Pago</a>

        <!-- Botón para imprimir deudas en PDF -->
		<a href="deudas_pdf.php?desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>" target="_blank" class="btn btn-primary mb-3">Ver PDF</a>

        <!-- Filtros de rango de fecha -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="desde" class="form-label">Desde</label>
                <input type="date" id="desde" name="desde" class="form-control" value="<?php echo $desde; ?>">
            </div>
            <div class="col-md-4">
                <label for="hasta" class="form-label">Hasta</label>
                <input type="date" id="hasta" name="hasta" class="form-control" value="<?php echo $hasta; ?>">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-success">Filtrar</button>
                <a href="pagos_deudas.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <table id="deudasTable" class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Notas</th>
                        <th>Monto</th>  <!-- Nueva columna "Monto" -->
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo $row["fecha"]; ?></td>
                            <td><?php echo $row["categoria"]; ?></td>
                            <td><?php echo $row["notas"]; ?></td>
                            <td><?php echo number_format($row["monto"], 0, '', '.'); ?></td> <!-- Mostrar el monto -->
                            <td>
                                <a href="editar_deuda.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <form action="eliminar_deuda.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta deuda?');">
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
            <p>No se encontraron deudas en el rango seleccionado.</p>
        <?php endif; ?>
    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir JS de DataTables -->
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <!-- Inicializar DataTables -->
    <script>
        $(document).ready(function() {
            $('#deudasTable').DataTable();
        });
    </script>
    
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
