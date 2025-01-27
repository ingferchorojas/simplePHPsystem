<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Verificar si se ha enviado el ID
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Consulta para eliminar la deuda
    $sql = "DELETE FROM deudas WHERE id = ?";
    
    // Preparar la consulta
    if ($stmt = $conn->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir al usuario después de la eliminación
            header("Location: pagos_deudas.php");
            exit();
        } else {
            echo "Error al eliminar la deuda.";
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
    }
} else {
    echo "ID no proporcionado.";
}

// Cerrar la conexión
$conn->close();
?>
