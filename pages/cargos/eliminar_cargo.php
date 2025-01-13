<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Verificar si se ha enviado el ID
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Consulta para marcar el cliente como eliminado
    $sql = "UPDATE cargos SET deleted = 1 WHERE id = ?";
    
    // Preparar la consulta
    if ($stmt = $conn->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir al usuario después de la eliminación
            header("Location: cargos.php"); // Asegúrate de que 'lista_clientes.php' sea el archivo correcto
            exit();
        } else {
            echo "Error al eliminar el cargo.";
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
