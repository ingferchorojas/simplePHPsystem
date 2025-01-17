<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $cliente_id = $_POST['cliente_id']; // ID del cliente seleccionado
    $fecha = $_POST['fecha'];
    $doc_num = $_POST['documento_numero'];
    $dias_credito = $_POST['dias_credito'];
    $cargo = $_POST['cargo'];
    $concepto = $_POST['concepto'];

    // Consulta para verificar si ya existe un documento con ese número
    $sql_check = "SELECT * FROM cargos WHERE numero_documento = '$doc_num'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Si el documento ya existe, mostrar un mensaje
        echo "<script>alert('El número de documento ya existe.'); window.location.href = 'agregar_cargo.php';</script>";
    } else {
        // Si no existe, insertar el nuevo cargo
        $sql = "INSERT INTO cargos (cliente_id, fecha, numero_documento, dias_credito, cargo, concepto) 
                VALUES ('$cliente_id', '$fecha', '$doc_num', '$dias_credito', '$cargo', '$concepto')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Cargo agregado'); window.location.href = 'cargos.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cargo</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        input[readonly], textarea[readonly] {
            background-color: #f5f5f5;
            border: 1px solid #dcdcdc;
            color: #888;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../pages/cargos/cargos.php">Lista de Cargos</a>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 600px;">
        <h2>Nuevo Cargo</h2>

        <form action="agregar_cargo.php" method="POST">
            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" id="cliente" class="form-control" placeholder="Buscar cliente" required readonly>
                <input type="hidden" name="cliente_id" id="cliente_id">
            </div>

            <button type="button" class="btn btn-info" onclick="abrirModalClientes()">Seleccionar Cliente</button>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="mb-3">
                <label for="doc_num" class="form-label">Documento</label>
                <input type="text" class="form-control" id="doc_num" name="documento_numero" required>
            </div>
            <div class="mb-3">
                <label for="dias_credito" class="form-label">Días de Crédito</label>
                <input type="number" class="form-control" id="dias_credito" name="dias_credito" required>
            </div>

            <!-- Campo de kg -->
            <div class="mb-3">
                <label for="kg" class="form-label">Kg</label>
                <input type="number" class="form-control" id="kg" name="kg" step="any" required oninput="calcularCargo()">
            </div>
            
            <!-- Campo de precio por kilo -->
            <div class="mb-3">
                <label for="precio_por_kilo" class="form-label">Precio x Kilo</label>
                <input type="number" class="form-control" id="precio_por_kilo" name="precio_por_kilo" step="any" required oninput="calcularCargo()">
            </div>
            
            <!-- Campo de cargo calculado -->
            <div class="mb-3">
                <label for="cargo" class="form-label">Cargo (Gs.)</label>
                <input type="number" class="form-control" id="cargo" name="cargo" required readonly>
            </div>

            <!-- Concepto más reducido -->
            <div class="mb-3">
                <label for="concepto" class="form-label">Concepto</label>
                <textarea class="form-control" id="concepto" name="concepto" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Agregar Cargo</button>
        </form>

        <a href="cargos.php" class="btn btn-secondary mt-3">Volver</a>
        <br>
        <br>
    </div>


    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="../../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función para cargar clientes con filtro
        function cargarClientes(filtro = '') {
            $.ajax({
                url: 'buscar_clientes.php', 
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
                                <td><button class="btn btn-success btn-sm select-client" data-id="${cliente.id}" data-nombre="${cliente.nombre}" data-apellido="${cliente.apellido}">Seleccionar</button></td>
                            </tr>
                        `;
                    });
                    $('#clientesTable tbody').html(clientesHtml);
                    $('#clientesTable').DataTable();
                }
            });
        }

        function abrirModalClientes() {
            var myModal = new bootstrap.Modal(document.getElementById('clientesModal'));
            myModal.show();
            cargarClientes();
        }

        $('#buscarCliente').on('keyup', function() {
            const filtro = $(this).val();
            cargarClientes(filtro);
        });

        $(document).on('click', '.select-client', function() {
            const clienteId = $(this).data('id');
            const clienteNombre = $(this).data('nombre');
            const clienteApellido = $(this).data('apellido');
            $('#cliente').val(clienteNombre + ' ' + clienteApellido);
            $('#cliente_id').val(clienteId);
            $('#clientesModal').modal('hide');
            
        });

        // Función para calcular el cargo
        function calcularCargo() {
            const kg = parseFloat(document.getElementById('kg').value);
            const precioPorKilo = parseFloat(document.getElementById('precio_por_kilo').value);
            
            if (!isNaN(kg) && !isNaN(precioPorKilo)) {
                const cargo = kg * precioPorKilo;
                document.getElementById('cargo').value = cargo.toFixed(0);

                // Concepto simplificado
                const concepto = `Gs. ${precioPorKilo.toFixed(0)}/Kg | Kg: ${kg} | Total: Gs. ${cargo.toFixed(0)}`;
                document.getElementById('concepto').value = concepto;
            }
        }
    </script>

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
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
