<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];

    // Consulta para insertar un nuevo cliente
    $sql = "INSERT INTO clientes (nombre, apellido, telefono) VALUES ('$nombre', '$apellido', '$telefono')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Cliente agregado correctamente'); window.location.href = 'clientes.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cliente</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../../assets/logo.png" type="image/png">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../pages/clientes/clientes.php">Lista de clientes</a>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 600px;">
        <h2>Formulario de Nuevo Cliente</h2>

        <form action="agregar_cliente.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" required>
            </div>
            <button type="submit" class="btn btn-primary">Agregar Cliente</button>
        </form>

        <a href="clientes.php" class="btn btn-secondary mt-3">Volver a la lista de clientes</a>
    </div>

    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
