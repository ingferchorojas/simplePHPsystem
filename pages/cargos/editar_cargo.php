<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Verificar si se ha recibido el ID del cargo para editar
if (isset($_GET['id'])) {
    $cargo_id = $_GET['id'];

    // Obtener los datos del cargo de la base de datos
    $sql = "SELECT * FROM cargos WHERE id = '$cargo_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $cargo = $result->fetch_assoc();
    } else {
        echo "<script>alert('Cargo no encontrado'); window.location.href = 'cargos.php';</script>";
        exit();
    }
}

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $cliente_id = $_POST['cliente_id'];
    $fecha = $_POST['fecha'];
    $documento_numero = $_POST['documento_numero'];
    $dias_credito = $_POST['dias_credito'];
    $cargo_valor = $_POST['cargo'];
    $concepto = $_POST['concepto'];
    $kg = $_POST['kg']; // Obtener los kg del formulario
    $cantidad_cerdos = $_POST['cantidad_cerdos'];
    $precio_por_kg = $_POST['precio_por_kilo'];

    // Consulta para actualizar el cargo
    $sql = "UPDATE cargos SET 
                cliente_id = '$cliente_id',
                fecha = '$fecha',
                numero_documento = '$documento_numero',
                dias_credito = '$dias_credito',
                cargo = '$cargo_valor',
                concepto = '$concepto',
                kg = '$kg',
                cantidad_cerdos = '$cantidad_cerdos',
                precio_por_kg = '$precio_por_kg'
            WHERE id = '$cargo_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Cargo actualizado correctamente'); window.location.href = 'cargos.php';</script>";
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
    <title>Editar Cargo</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="icon" href="../../assets/logo.png" type="image/png">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../pages/cargos/cargos.php">Lista de Cargos</a>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 600px;">
        <h2>Editar Cargo</h2>

        <form action="editar_cargo.php?id=<?php echo $cargo['id']; ?>" method="POST">
            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" id="cliente" class="form-control" value="<?php echo $cargo['cliente_id']; ?>" readonly required>
                <input type="hidden" name="cliente_id" id="cliente_id" value="<?php echo $cargo['cliente_id']; ?>">
            </div>
            <button type="button" class="btn btn-info" onclick="abrirModalClientes()">Seleccionar Cliente</button>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $cargo['fecha']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="documento_numero" class="form-label">Número de Documento</label>
                <input type="text" class="form-control" id="documento_numero" name="documento_numero" value="<?php echo $cargo['numero_documento']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="dias_credito" class="form-label">Días de Crédito</label>
                <input type="number" class="form-control" id="dias_credito" name="dias_credito" value="<?php echo $cargo['dias_credito']; ?>" required>
            </div>
             <!-- Campo de kg -->
             <div class="mb-3">
                <label for="kg" class="form-label">Kg</label>
                <input type="number" class="form-control" id="kg" name="kg" value="<?php echo $cargo['kg']; ?>" required>
            </div>
            <!-- Campo de precio por kilo -->
            <div class="mb-3">
                <label for="precio_por_kilo" class="form-label">Precio x Kilo</label>
                <input type="number" class="form-control" id="precio_por_kilo" name="precio_por_kilo" value="<?php echo $cargo['precio_por_kg']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="cantidad_cerdos" class="form-label">Cantidad de cerdos</label>
                <input type="number" class="form-control" id="cantidad_cerdos" name="cantidad_cerdos" value="<?php echo $cargo['cantidad_cerdos']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="cargo" class="form-label">Cargo (en Guaraníes)</label>
                <input type="number" class="form-control" id="cargo" name="cargo" value="<?php echo $cargo['cargo']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="concepto" class="form-label">Concepto</label>
                <textarea class="form-control" id="concepto" name="concepto" required><?php echo $cargo['concepto']; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>

        <a href="cargos.php" class="btn btn-secondary mt-3">Volver a la lista de cargos</a>
        <br>
        <br>
    </div>

    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
