<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Consulta para obtener los clientes
$sql = "SELECT id, nombre, apellido, telefono FROM clientes WHERE deleted = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
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
        <h2>Lista de Clientes</h2>
        
        <!-- Botón para agregar cliente -->
        <a href="agregar_cliente.php" class="btn btn-primary mb-3">Agregar Cliente</a>

        <?php if ($result->num_rows > 0): ?>
            <table id="clientesTable" class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Teléfono</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo $row["nombre"]; ?></td>
                            <td><?php echo $row["apellido"]; ?></td>
                            <td><?php echo $row["telefono"]; ?></td>
                            <td>
                                <!-- Enlace para editar con ícono -->
                                <a href="editar_cliente.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <!-- Formulario para eliminar (marcar como eliminado) con ícono -->
                                <form action="eliminar_cliente.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
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
            <p>No se encontraron clientes.</p>
        <?php endif; ?>

    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir JS de DataTables -->
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <!-- Inicializar DataTables -->
    <script>
        $(document).ready(function() {
            $('#clientesTable').DataTable(); // Inicializa DataTables en la tabla con el ID "clientesTable"
        });
    </script>
    
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
