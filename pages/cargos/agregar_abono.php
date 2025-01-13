<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $cliente_id = $_POST['cliente_id'];  // ID del cliente
    $documento_numero = $_POST['documento_numero'];  // Número de documento
    $fecha = $_POST['fecha'];
    $monto_abono = $_POST['monto_abono'];
    $nota = $_POST['nota'];

    // Verificar si el número de documento está vacío
    if (empty($documento_numero)) {
        echo "<script>alert('Debe seleccionar un número de documento válido.'); window.history.back();</script>";
        exit;
    }

    // Validar si $monto_abono es un valor numérico
    if (!is_numeric($monto_abono) || $monto_abono <= 0) {
        echo "<script>alert('El monto del abono debe ser un número válido mayor a 0.'); window.history.back();</script>";
        exit;
    }

    // Consulta para insertar un nuevo abono
    $sql = "INSERT INTO abonos (cliente_id, numero_documento, fecha, monto_abono, nota) 
            VALUES ('$cliente_id', '$documento_numero', '$fecha', '$monto_abono', '$nota')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Abono agregado correctamente'); window.location.href = 'cargos.php';</script>";
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
    <title>Agregar Abono</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../pages/cargos/cargos.php">Lista de Cargos</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Formulario de Nuevo Abono</h2>

        <form action="agregar_abono.php" method="POST">
            <!-- Campo para seleccionar cliente -->
            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" id="cliente" class="form-control" placeholder="Seleccione un cliente" readonly>
                <input type="hidden" name="cliente_id" id="cliente_id"> <!-- Campo oculto para el ID del cliente -->
                <input type="hidden" name="numero_documento" id="numero_documento"> <!-- Campo oculto para el número de documento -->
            </div>

            <!-- Botón para abrir el modal de selección de cliente -->
            <button type="button" class="btn btn-info mb-3" onclick="abrirModalClientes()">Seleccionar Cliente</button>

            <!-- Campo para mostrar y seleccionar documento número -->
            <div class="mb-3">
                <label for="documento_numero" class="form-label">Número de Documento</label>
                <select class="form-control" id="documento_numero" name="documento_numero" required>
                    <option value="" disabled selected>Seleccione un número de documento</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="mb-3">
                <label for="monto_abono" class="form-label">Monto del Abono (en Guaraníes)</label>
                <input type="number" class="form-control" id="monto_abono" name="monto_abono" required>
            </div>
            <div class="mb-3">
                <label for="nota" class="form-label">Nota</label>
                <textarea class="form-control" id="nota" name="nota" placeholder="Opcional: Detalles sobre el abono"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Agregar Abono</button>
        </form>

        <a href="cargos.php" class="btn btn-secondary mt-3">Volver a la lista de abonos</a>
    </div>

    <!-- Modal para mostrar la lista de clientes -->
    <div class="modal fade" id="clientesModal" tabindex="-1" aria-labelledby="clientesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientesModalLabel">Seleccionar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="buscarCliente" class="form-control mb-3" placeholder="Buscar cliente por nombre o teléfono">
                    <table class="table table-bordered" id="clientesTable">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Teléfono</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se cargarán los clientes dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir jQuery y Bootstrap JS -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        // Cargar clientes en el modal
        function cargarClientes(filtro = '') {
            $.ajax({
                url: 'buscar_clientes.php', // Archivo para buscar clientes
                method: 'GET',
                data: { filtro: filtro },
                success: function(data) {
                    const clientes = JSON.parse(data);
                    let clientesHtml = '';
                    clientes.forEach(cliente => {
                        clientesHtml += `
                            <tr>
                                <td>${cliente.nombre}</td>
                                <td>${cliente.apellido}</td>
                                <td>${cliente.telefono}</td>
                                <td><button class="btn btn-success btn-sm select-client" data-id="${cliente.id}" data-nombre="${cliente.nombre} ${cliente.apellido}">Seleccionar</button></td>
                            </tr>
                        `;
                    });
                    $('#clientesTable tbody').html(clientesHtml);
                }
            });
        }

        function abrirModalClientes() {
            const modal = new bootstrap.Modal(document.getElementById('clientesModal'));
            modal.show();
            cargarClientes();
        }

        // Filtrar clientes en tiempo real
        $('#buscarCliente').on('keyup', function() {
            cargarClientes($(this).val());
        });

        // Seleccionar cliente y cargar números de documento
        $(document).on('click', '.select-client', function() {
            const clienteId = $(this).data('id');
            const clienteNombre = $(this).data('nombre');
            $('#cliente').val(clienteNombre);
            $('#cliente_id').val(clienteId);

            // Cargar números de documento del cliente seleccionado
            $.ajax({
                url: 'obtener_documentos.php', // Archivo que obtiene documentos del cliente
                method: 'GET',
                data: { cliente_id: clienteId },
                success: function(data) {
                    const documentos = JSON.parse(data);
                    let documentosHtml = '<option value="" disabled selected>Seleccione un número de documento</option>';
                    documentos.forEach(doc => {
                        documentosHtml += `<option value="${doc.numero_documento}">${doc.numero_documento}</option>`;
                    });
                    $('#documento_numero').html(documentosHtml);
                }
            });

            // Cerrar modal
            $('#clientesModal').modal('hide');
        });
    </script>
</body>
</html>
