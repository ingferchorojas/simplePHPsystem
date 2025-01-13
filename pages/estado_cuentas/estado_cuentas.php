<?php
// Incluir la conexión a la base de datos
include('../../config/db.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $cliente_id = $_POST['cliente_id']; // ID del cliente seleccionado
    $fecha_desde = $_POST['fecha_desde']; // Fecha desde
    $fecha_hasta = $_POST['fecha_hasta']; // Fecha hasta

    // Consulta para establecer las variables
    $sql_set = "
        SET @cliente_id = '$cliente_id';
        SET @fecha_desde = '$fecha_desde';
        SET @fecha_hasta = '$fecha_hasta';
    ";

    // Ejecutar la consulta para establecer las variables
    if (!$conn->multi_query($sql_set)) {
        die("Error al establecer variables: " . $conn->error);
    }

    // Esperar a que la ejecución de multi_query termine antes de hacer la consulta principal
    do {
        // Si la ejecución de multi_query tiene resultados, los descarta
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());

    // Consulta principal para obtener los cargos y abonos
    $sql = "
        -- Cargar los cargos
        SELECT 
            cl.nombre AS cliente_nombre,
            cl.apellido AS cliente_apellido,
            ca.fecha AS fecha,
            'Cargo' AS concepto,
            ca.numero_documento AS documento,
            ca.cargo AS cargo,
            0 AS abono,
            ca.cargo AS subtotal
        FROM 
            clientes cl
        JOIN 
            cargos ca ON cl.id = ca.cliente_id 
        WHERE 
            cl.id = @cliente_id
            AND ca.deleted = 0
            AND ca.fecha BETWEEN @fecha_desde AND @fecha_hasta

        UNION ALL

        -- Cargar los abonos, pero solo si existe un cargo previo
        SELECT 
            cl.nombre AS cliente_nombre,
            cl.apellido AS cliente_apellido,
            ab.fecha AS fecha,
            'Abono' AS concepto,
            ab.numero_documento AS documento,
            0 AS cargo,
            ab.monto_abono AS abono,
            ab.monto_abono AS subtotal
        FROM 
            clientes cl
        JOIN 
            abonos ab ON cl.id = ab.cliente_id 
        WHERE 
            cl.id = @cliente_id
            AND ab.deleted = 0
            AND ab.fecha BETWEEN @fecha_desde AND @fecha_hasta
            AND EXISTS (
                SELECT 1
                FROM cargos ca 
                WHERE ca.cliente_id = cl.id 
                AND ca.fecha <= ab.fecha
                AND ca.deleted = 0
            )

        ORDER BY 
            fecha;
    ";

    // Ejecutar la consulta principal y obtener los resultados
    $result = $conn->query($sql);
    $movimientos = [];
    $total_cargos = 0;
    $total_abonos = 0;

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
            // Sumar los cargos y abonos
            $total_cargos += $row['cargo'];
            $total_abonos += $row['abono'];
        }
    }
    // Calcular el total adeudado
    $total_adeudado = $total_cargos - $total_abonos;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuentas</title>
    <!-- Incluir Bootstrap para los estilos -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluir CSS de DataTables -->
    <link href="../../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../..">Inicio</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Estado de Cuentas</h2>

        <!-- Formulario para seleccionar fechas y cliente -->
        <form action="estado_cuentas.php" method="POST">
            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" id="cliente" class="form-control" placeholder="Buscar cliente por nombre, apellido o teléfono" required readonly>
                <input type="hidden" name="cliente_id" id="cliente_id"> <!-- Campo oculto para el ID del cliente -->
            </div>

            <!-- Botón para abrir el modal de clientes -->
            <button type="button" class="btn btn-info" onclick="abrirModalClientes()">Seleccionar Cliente</button>

            <div class="mb-3">
                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" required>
            </div>

            <div class="mb-3">
                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" required>
            </div>

            <button type="submit" class="btn btn-primary">Consultar</button>
        </form>

        

        <!-- Mostrar los resultados -->
        <?php if (isset($movimientos) && count($movimientos) > 0): ?>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Número Documento</th>
                    <th>Cargo</th>
                    <th>Abono</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimientos as $movimiento): ?>
                <tr>
                    <td><?php echo $movimiento['fecha']; ?></td>
                    <td><?php echo $movimiento['concepto']; ?></td>
                    <td><?php echo $movimiento['documento']; ?></td>
                    <td><?php echo number_format($movimiento['cargo'], 0, ',', '.'); ?></td>
                    <td><?php echo number_format($movimiento['abono'], 0, ',', '.'); ?></td>
                    <td><?php echo number_format($movimiento['subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Mostrar el total adeudado -->
        <div class="mt-3">
            <h4>Total adeudado: <?php echo number_format($total_adeudado, 0, ',', '.'); ?></h4>
            <!-- Botón para imprimir saldos en pdf -->
            <a href="estado_cuentas_pdf.php?cliente_id=<?php echo $cliente_id; ?>&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>" target="_blank" class="btn btn-primary mb-3">Ver PDF</a>
        </div>

        <?php else: ?>
            <p>No se encontraron movimientos para este cliente en el rango de fechas seleccionado.</p>
        <?php endif; ?>
        
        <a href="../.." class="btn btn-secondary mt-3">Volver al inicio</a>
    </div>

    <!-- Incluir jQuery -->
    <script src="../../assets/jquery/jquery-3.6.0.min.js"></script>
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
            var myModal = new bootstrap.Modal(document.getElementById('clientesModal'));
            myModal.show();

            cargarClientes(); // Cargar los clientes en el modal cuando se abre
        }

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
