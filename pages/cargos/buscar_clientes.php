<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha recibido el parámetro de filtro
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

// Realizar la consulta para obtener los clientes que coincidan con el filtro
$sql = "SELECT id, nombre, apellido, telefono FROM clientes WHERE (nombre LIKE ? OR apellido LIKE ? OR telefono LIKE ?) AND deleted = 0";
$stmt = $conn->prepare($sql);
$likeFiltro = "%$filtro%";
$stmt->bind_param("sss", $likeFiltro, $likeFiltro, $likeFiltro);
$stmt->execute();
$result = $stmt->get_result();

// Crear un array para almacenar los resultados
$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

// Devolver los clientes como JSON
echo json_encode($clientes);
?>
