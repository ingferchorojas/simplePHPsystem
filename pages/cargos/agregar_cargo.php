<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $cliente_id = $_POST['cliente_id']; // ID del cliente seleccionado
    $fecha = $_POST['fecha'];
    $documento_numero = $_POST['documento_numero'];
    $dias_credito = $_POST['dias_credito'];
    $cargo = $_POST['cargo'];
    $concepto = $_POST['concepto'];

    // Consulta para insertar un nuevo cargo
    $sql = "INSERT INTO cargos (cliente_id, fecha, numero_documento, dias_credito, cargo, concepto) 
            VALUES ('$cliente_id', '$fecha', '$documento_numero', '$dias_credito', '$cargo', '$concepto')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Cargo agregado correctamente'); window.location.href = 'cargos.php';</script>";
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
    <title>Agregar Cargo</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluir CSS de DataTables -->
    <link href="../../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../pages/cargos/cargos.php">Lista de Cargos</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Formulario de Nuevo Cargo</h2>

        <form action="agregar_cargo.php" method="POST">
            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" id="cliente" class="form-control" placeholder="Buscar cliente por nombre, apellido o teléfono" required readonly>
                <input type="hidden" name="cliente_id" id="cliente_id"> <!-- Campo oculto para el ID del cliente -->
            </div>

            <!-- Botón para abrir el modal de clientes -->
            <button type="button" class="btn btn-info" onclick="abrirModalClientes()">Seleccionar Cliente</button>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="mb-3">
                <label for="documento_numero" class="form-label">Número de Documento</label>
                <input type="text" class="form-control" id="documento_numero" name="documento_numero" required>
            </div>
            <div class="mb-3">
                <label for="dias_credito" class="form-label">Días de Crédito</label>
                <input type="number" class="form-control" id="dias_credito" name="dias_credito" required>
            </div>
            <div class="mb-3">
                <label for="cargo" class="form-label">Cargo (en Guaraníes)</label>
                <input type="number" class="form-control" id="cargo" name="cargo" required>
            </div>
            <div class="mb-3">
                <label for="concepto" class="form-label">Concepto</label>
                <textarea class="form-control" id="concepto" name="concepto" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Agregar Cargo</button>
        </form>

        <a href="cargos.php" class="btn btn-secondary mt-3">Volver a la lista de cargos</a>
    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Incluir JS de DataTables -->
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <!-- Incluir Bootstrap JS -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función para cargar clientes con filtro
        function cargarClientes(filtro = '') {
            $.ajax({
                url: 'buscar_clientes.php', // Archivo que realiza la búsqueda de clientes
                method: 'GET',
                data: { filtro: filtro },
                success: function(data) {
                    console.log(data)
                    const clientes = JSON.parse(data);
                    let clientesHtml = '';
                    clientes.forEach(cliente => {
                        clientesHtml += `
                            <tr>
                                <td>${cliente.nombre}</td>
                                <td>${cliente.apellido}</td>
                                <td>${cliente.telefono}</td>
                                <td><button class="btn btn-success btn-sm select-client" data-id="${cliente.id}" data-nombre="${cliente.nombre}" data-apellido="${cliente.apellido}">Seleccionar</button></td>
                            </tr>
                        `;
                    });
                    $('#clientesTable tbody').html(clientesHtml);
                    $('#clientesTable').DataTable(); // Inicializa DataTables después de cargar los clientes
                }
            });
        }

        function abrirModalClientes() {
            // Abre el modal manualmente
            var myModal = new bootstrap.Modal(document.getElementById('clientesModal'));
            myModal.show();

            console.log("Modal abierto, cargando clientes...");
            cargarClientes(); // Cargar los clientes en el modal cuando se abre
        }

        // Filtrar clientes según la búsqueda
        $('#buscarCliente').on('keyup', function() {
            const filtro = $(this).val();
            cargarClientes(filtro);
        });

        // Seleccionar cliente
        $(document).on('click', '.select-client', function() {
            const clienteId = $(this).data('id');
            const clienteNombre = $(this).data('nombre');
            const clienteApellido = $(this).data('apellido');
            $('#cliente').val(clienteNombre + ' ' + clienteApellido);
            $('#cliente_id').val(clienteId);
            $('#clientesModal').modal('hide');
        });
    </script>

    <!-- Modal para mostrar la lista de clientes -->
    <div class="modal fade" id="clientesModal" tabindex="-1" aria-labelledby="clientesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientesModalLabel">Seleccionar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
</body>
</html>
