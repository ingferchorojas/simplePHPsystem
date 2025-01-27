<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $fecha = $_POST['fecha'];
    $categoria = $_POST['categoria'];
    $notas = $_POST['notas'];
    $monto = $_POST['monto'];  // Obtener el monto desde el formulario

    // Consulta para insertar una nueva deuda
    $sql = "INSERT INTO deudas (fecha, categoria, notas, monto) VALUES ('$fecha', '$categoria', '$notas', '$monto')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Deuda agregada correctamente'); window.location.href = 'pagos_deudas.php';</script>";
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
    <title>Agregar Deuda</title>
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
        <h2>Formulario de Nuevo Pago</h2>

        <form action="agregar_deuda.php" method="POST">
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-control" id="categoria" name="categoria" required>
                    <option value="" disabled selected>Seleccione una categoría</option>
                    <option value="Pago de personal">Pago de personal</option>
                    <option value="Pago de deudas">Pago de deudas</option>
                    <option value="Pago de fletes">Pago de fletes</option>
                    <option value="Posibles pagos">Posibles pagos</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notas" class="form-label">Notas</label>
                <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="monto" class="form-label">Monto</label>
                <input type="number" class="form-control" id="monto" name="monto" required step="0.01" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Agregar Deuda</button>
        </form>

        <a href="pagos_deudas.php" class="btn btn-secondary mt-3">Volver a la lista de deudas</a>
    </div>

    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
