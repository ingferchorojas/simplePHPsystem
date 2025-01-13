<?php
include('../../config/db.php');

if (isset($_GET['cliente_id'])) {
    $cliente_id = $_GET['cliente_id'];
    $query = "SELECT numero_documento FROM cargos WHERE cliente_id = '$cliente_id'";
    $result = $conn->query($query);

    $documentos = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $documentos[] = $row;
        }
    }
    echo json_encode($documentos);
}
?>
