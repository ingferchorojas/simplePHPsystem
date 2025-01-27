<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Verificar si se ha enviado el ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para obtener los datos de la deuda, incluyendo el monto
    $sql = "SELECT id, fecha, categoria, notas, monto FROM deudas WHERE id = ?";
    
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
            echo "Deuda no encontrada.";
            exit();
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
        exit();
    }
} else {
    echo "ID de la deuda no proporcionado.";
    exit();
}

// Verificar si se ha enviado el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $categoria = $_POST['categoria'];
    $notas = $_POST['notas'];
    $monto = $_POST['monto'];  // Obtener el monto del formulario

    // Consulta para actualizar los datos de la deuda
    $sql = "UPDATE deudas SET fecha = ?, categoria = ?, notas = ?, monto = ? WHERE id = ?";

    // Preparar la consulta
    if ($stmt = $conn->prepare($sql)) {
        // Vincular los parámetros, incluyendo el monto
        $stmt->bind_param("sssii", $fecha, $categoria, $notas, $monto, $id);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir al usuario después de la actualización
            header("Location: pagos_deudas.php");
            exit();
        } else {
            echo "Error al actualizar la deuda.";
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
    <title>Editar Deuda</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../../assets/logo.png" type="image/png">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="pagos_deudas.php">Lista de pagos</a>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 600px;">
        <h2>Editar Pago</h2>

        <!-- Formulario para editar deuda -->
        <form action="editar_deuda.php?id=<?php echo $id; ?>" method="POST">
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $row['fecha']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-control" id="categoria" name="categoria" required>
                    <option value="Pago de personal" <?php if ($row['categoria'] == 'Pago de personal') echo 'selected'; ?>>Pago de personal</option>
                    <option value="Pago de deudas" <?php if ($row['categoria'] == 'Pago de deudas') echo 'selected'; ?>>Pago de deudas</option>
                    <option value="Pago de fletes" <?php if ($row['categoria'] == 'Pago de fletes') echo 'selected'; ?>>Pago de fletes</option>
                    <option value="Posibles pagos" <?php if ($row['categoria'] == 'Posibles pagos') echo 'selected'; ?>>Posibles pagos</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notas" class="form-label">Notas</label>
                <textarea class="form-control" id="notas" name="notas"><?php echo $row['notas']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="monto" class="form-label">Monto</label>
                <input type="number" class="form-control" id="monto" name="monto" value="<?php echo $row['monto']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="deudas.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
