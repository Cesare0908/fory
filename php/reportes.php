<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Sistema de Entregas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="../css/reportes.css">
</head>
<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-chart-bar me-2"></i>Reporte de Ventas</h1>
            <div class="button-group">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" id="rangoFechas" class="form-control" placeholder="Seleccionar rango de fechas">
                </div>
                <button class="btn btn-custom" id="actualizarReporte"><i class="fas fa-sync-alt me-2"></i>Actualizar</button>
            </div>
        </div>

        <!-- Gráfico de Ventas por Categoría -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Ventas por Categoría</h5>
                        <canvas id="graficoVentasPorCategoria"></canvas>
                    </div>
                </div>
            </div>
            <!-- Gráfico de Ventas por Día -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Ventas por Día</h5>
                        <canvas id="graficoVentasPorDia"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Productos Más Vendidos -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <h5 class="card-title p-3"><i class="fas fa-table me-2"></i>Productos Más Vendidos</h5>
                        <div id="tablaProductosMasVendidos"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Productos Menos Vendidos -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <h5 class="card-title p-3"><i class="fas fa-table me-2"></i>Productos Menos Vendidos</h5>
                        <div id="tablaProductosMenosVendidos"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="../js/funcionesReportes.js?v=1"></script>
</body>
</html>