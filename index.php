<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de cuentas</title>
    <!-- Bootstrap CSS local -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS local -->
    <link href="assets/fontawesome/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="assets/logo.png" type="image/png">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="./">Control de cuentas</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <!-- Card 1: Clientes -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5 class="card-title">Clientes</h5>
                        <p class="card-text">Gestiona todos los clientes de tu sistema.</p>
                        <a href="pages/clientes/clientes.php" class="btn btn-primary">Ver Clientes</a>
                    </div>
                </div>
            </div>

            <!-- Card 2: Registrar crédito / Abono -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                        <h5 class="card-title">Registrar Crédito / Abono</h5>
                        <p class="card-text">Registra los créditos o abonos realizados por los clientes.</p>
                        <a href="pages/cargos/cargos.php" class="btn btn-primary">Registrar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Card 3: Antigüedad de saldos -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                        <h5 class="card-title">Saldos</h5>
                        <p class="card-text">Consulta los saldos de los clientes.</p>
                        <a href="pages/saldos/saldos.php" class="btn btn-primary">Ver Saldos</a>
                    </div>
                </div>
            </div>

            <!-- Card 4: Estado de cuentas -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <h5 class="card-title">Estado de Cuentas</h5>
                        <p class="card-text">Consulta el estado de las cuentas de los clientes.</p>
                        <a href="pages/estado_cuentas/estado_cuentas.php" class="btn btn-primary">Ver Estado</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Card 5: Pago de deudas -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                        <h5 class="card-title">Pago de deudas</h5>
                        <p class="card-text">Realiza pagos para saldar las deudas de los clientes.</p>
                        <a href="pages/pagos_deudas/pagos_deudas.php" class="btn btn-primary">Ver Pagos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS local -->
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
