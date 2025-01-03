<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Verificar si se ha enviado el ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para obtener los datos del cliente
    $sql = "SELECT id, nombre, apellido, telefono FROM clientes WHERE id = ? AND deleted = 0";
    
    // Preparar la consulta
    if ($stmt = $conn->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener los resultados
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            echo "Cliente no encontrado o ya ha sido eliminado.";
            exit();
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
        exit();
    }
} else {
    echo "ID del cliente no proporcionado.";
    exit();
}

// Verificar si se ha enviado el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];

    // Consulta para actualizar los datos del cliente
    $sql = "UPDATE clientes SET nombre = ?, apellido = ?, telefono = ? WHERE id = ?";

    // Preparar la consulta
    if ($stmt = $conn->prepare($sql)) {
        // Vincular los parámetros
        $stmt->bind_param("sssi", $nombre, $apellido, $telefono, $id);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir al usuario después de la actualización
            header("Location: clientes.php");
            exit();
        } else {
            echo "Error al actualizar el cliente.";
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
    }
}

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
        <a class="navbar-brand" href="../../pages/clientes/clientes.php">Lista de clientes</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Editar Cliente</h2>

        <!-- Formulario para editar cliente -->
        <form action="editar_cliente.php?id=<?php echo $id; ?>" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $row['nombre']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo $row['apellido']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $row['telefono']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
