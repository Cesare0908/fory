<?php include '../config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Entregas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE ?>css/dashboard.css?v=5">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/1053/1053244.png" type="image/png">
  
</head>
<body class="container-fluid p-0">
    <main class="main-container p-4" style="background-color: #F0F8F8;">
        <div class="header-container mb-4 p-3 bg-teal text-white rounded-top" style="border-bottom: 2px solid #006666;">
            <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard de Entregas</h1>
            <div class="button-group">
                <button class="btn btn-custom" id="refreshDashboard"><i class="fas fa-sync-alt me-2"></i>Actualizar</button>
            </div>
        </div>

        <!-- Tarjetas de Métricas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-teal"><i class="fas fa-shopping-cart me-2"></i>Total Pedidos</h5>
                        <h3 id="totalPedidos" class="text-teal">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-teal"><i class="fas fa-dollar-sign me-2"></i>Ingresos Totales</h5>
                        <h3 id="ingresosTotales" class="text-teal">$0.00</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-teal"><i class="fas fa-hourglass-half me-2"></i>Pedidos Pendientes</h5>
                        <h3 id="pedidosPendientes" class="text-teal">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-teal"><i class="fas fa-user me-2"></i>Repartidores Activos</h5>
                        <h3 id="repartidoresActivos" class="text-teal">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-teal"><i class="fas fa-clock me-2"></i>Tiempo Promedio Entrega</h5>
                        <h3 id="tiempoPromedioEntrega" class="text-teal">0 min</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-teal"><i class="fas fa-check-circle me-2"></i>Entregas a Tiempo</h5>
                        <h3 id="tasaEntregasATiempo" class="text-teal">0%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primera fila: Productos más vendidos e Ingresos -->
        <div class="row g-4 mb-4">
            <!-- Gráfica de Productos Más Vendidos -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-chart-bar me-2"></i>Productos Más Vendidos</h5>
                        <canvas id="productosMasVendidosChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Gráfica de Ingresos -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-chart-line me-2"></i>Ingresos</h5>
                        <div class="mb-3">
                            <label for="periodoIngresos" class="form-label text-teal">Período:</label>
                            <select id="periodoIngresos" class="form-select" style="max-width: 200px;">
                                <option value="dia">Por Día</option>
                                <option value="mes">Por Mes</option>
                                <option value="anio">Por Año</option>
                            </select>
                            <label for="fechaInicio" class="form-label text-teal mt-2">Fecha Inicio:</label>
                            <input type="date" id="fechaInicio" class="form-control" style="max-width: 200px;">
                            <label for="fechaFin" class="form-label text-teal mt-2">Fecha Fin:</label>
                            <input type="date" id="fechaFin" class="form-control" style="max-width: 200px;">
                            <button class="btn btn-custom mt-2" id="filtrarIngresos"><i class="fas fa-filter me-2"></i>Filtrar</button>
                        </div>
                        <canvas id="ingresosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda fila: Productos menos vendidos y Zonas de entrega populares -->
        <div class="row g-4 mb-4">
            <!-- Gráfica de Productos Menos Vendidos -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-chart-bar me-2"></i>Productos Menos Vendidos</h5>
                        <canvas id="productosMenosVendidosChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Gráfica de Zonas de Entrega Populares -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-map-marker-alt me-2"></i>Zonas de Entrega Populares</h5>
                        <canvas id="zonasEntregaPopularesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Otras gráficas y tablas -->
        <div class="row g-4">
            <!-- Gráfica de Clientes Principales -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-users me-2"></i>Clientes Principales</h5>
                        <canvas id="clientesPrincipalesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Gráfica de Pedidos por Estado -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-chart-pie me-2"></i>Pedidos por Estado</h5>
                        <canvas id="pedidosPorEstadoChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Gráfica de Ventas por Categoría -->
            <div class="col-lg-6">
                <div class="card card-custom shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-teal"><i class="fas fa-tags me-2"></i>Ventas por Categoría</h5>
                        <canvas id="ventasPorCategoriaChart"></canvas>
                    </div>
                </div>
            </div> 
            
            <!-- Tablas con Exportación -->
            <div class="col-12">
                <div class="card card-custom shadow-sm">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="card-title text-teal"><i class="fas fa-table me-2"></i>Productos Más Vendidos</h5>
                            <div>
                                <button class="btn btn-export" onclick="exportTableToExcel('productosMasVendidosTable', 'Productos_Mas_Vendidos')"><i class="fas fa-file-excel me-2"></i>Excel</button>
                                <button class="btn btn-export" onclick="exportTableToPDF('productosMasVendidosTable', 'Productos Más Vendidos')"><i class="fas fa-file-pdf me-2"></i>PDF</button>
                            </div>
                        </div>
                        <div id="productosMasVendidosTable"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="card card-custom shadow-sm">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="card-title text-teal"><i class="fas fa-users me-2"></i>Clientes Principales</h5>
                            <div>
                                <button class="btn btn-export" onclick="exportTableToExcel('clientesPrincipalesTable', 'Clientes_Principales')"><i class="fas fa-file-excel me-2"></i>Excel</button>
                                <button class="btn btn-export" onclick="exportTableToPDF('clientesPrincipalesTable', 'Clientes Principales')"><i class="fas fa-file-pdf me-2"></i>PDF</button>
                            </div>
                        </div>
                        <div id="clientesPrincipalesTable"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="card card-custom shadow-sm">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="card-title text-teal"><i class="fas fa-exclamation-triangle me-2"></i>Alertas de Stock Bajo</h5>
                            <div>
                                <button class="btn btn-export" onclick="exportTableToExcel('alertasStockBajoTable', 'Alertas_Stock_Bajo')"><i class="fas fa-file-excel me-2"></i>Excel</button>
                                <button class="btn btn-export" onclick="exportTableToPDF('alertasStockBajoTable', 'Alertas de Stock Bajo')"><i class="fas fa-file-pdf me-2"></i>PDF</button>
                            </div>
                        </div>
                        <div id="alertasStockBajoTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="<?= URL_BASE ?>js/FuncionesAdmin/funcionesDashboard.js?v=106"></script>
</body>
</html>