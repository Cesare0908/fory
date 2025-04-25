<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Entregas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard de Entregas</h1>
            <div class="button-group">
                <button class="btn btn-custom" id="refreshDashboard"><i class="fas fa-sync-alt me-2"></i>Actualizar</button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fas fa-shopping-cart me-2"></i>Total Pedidos</h5>
                        <h3 id="totalOrders" class="text-primary">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fas fa-dollar-sign me-2"></i>Ingresos Totales</h5>
                        <h3 id="totalRevenue" class="text-success">$0.00</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fas fa-hourglass-half me-2"></i>Pedidos Pendientes</h5>
                        <h3 id="pendingOrders" class="text-warning">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fas fa-user me-2"></i>Repartidores Activos</h5>
                        <h3 id="activeDelivery" class="text-info">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Table -->
        <div class="row g-4">
            <!-- Pie Chart: Orders by Status -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Pedidos por Estado</h5>
                        <canvas id="ordersByStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Line Chart: Revenue Over Time -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Ingresos por Día</h5>
                        <canvas id="revenueOverTimeChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Table: Top Products -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <h5 class="card-title p-3"><i class="fas fa-table me-2"></i>Productos Más Vendidos</h5>
                        <div id="topProductsTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../js/funcionesDashboard.js"></script>
</body>
</html>