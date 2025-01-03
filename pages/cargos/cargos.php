<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Consulta para obtener los cargos
$sql = "SELECT c.fecha, c.cliente_id, cl.nombre, cl.apellido, c.numero_documento, c.dias_credito, c.cargo, c.concepto
        FROM cargos c
        INNER JOIN clientes cl ON c.cliente_id = cl.id
        WHERE c.deleted = 0";
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

        <?php if ($result->num_rows > 0): ?>
            <table id="cargosTable" class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Días de Crédito</th>
                        <th>Cargo</th>
                        <th>Concepto</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
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
                            <td>
                                <!-- Enlace para editar con ícono -->
                                <a href="editar_cargo.php?id=<?php echo $row['cliente_id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <!-- Formulario para eliminar (marcar como eliminado) con ícono -->
                                <form action="eliminar_cargo.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cargo?');">
                                    <input type="hidden" name="id" value="<?php echo $row['cliente_id']; ?>">
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
