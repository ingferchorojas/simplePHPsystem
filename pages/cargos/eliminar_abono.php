<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Verificar si se ha recibido un id de abono
if (isset($_POST['id']) && isset($_POST['cargo_id'])) {
    $id_abono = $_POST['id'];
    $cargo_id = $_POST['cargo_id'];

    // Consulta para eliminar el abono
    $sql = "DELETE FROM abonos WHERE id = $id_abono";

    if ($conn->query($sql) === TRUE) {
        // Si se eliminó correctamente, redirigir al usuario con un mensaje de éxito
        echo "<script>
                alert('Abono eliminado exitosamente');
                window.location.href = 'ver_detalles.php?id=" . $cargo_id . "';
              </script>";
    } else {
        // Si hay un error, mostrar un mensaje de error
        echo "<script>
                alert('Error al eliminar el abono: " . $conn->error . "');
                window.location.href = 'ver_detalles.php?id=" . $cargo_id . "';
              </script>";
    }
} else {
    // Si no se ha recibido un id, redirigir al usuario
    echo "<script>
            alert('No se proporcionó un ID de abono');
            window.location.href = 'ver_detalles.php?id=" . $cargo_id . "';
          </script>";
}

$conn->close();

?>
