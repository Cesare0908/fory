<?php include '../config.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE ?>css/productos.css">
</head>

<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-shopping-cart me-2"></i>GESTIÓN DE PEDIDOS</h1>
            <div class="button-group">
                <div class="dropdown">
                    <button class="btn btn-custom dropdown-toggle" type="button" id="filtroEstado" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter me-2"></i>Filtrar por Estado
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filtroEstado">
                        <li><a class="dropdown-item" href="#" data-estado="">Todos</a></li>
                        <li><a class="dropdown-item" href="#" data-estado="pendiente">Pendiente</a></li>
                        <li><a class="dropdown-item" href="#" data-estado="proceso">En proceso</a></li>
                        <li><a class="dropdown-item" href="#" data-estado="camino">En camino</a></li>
                        <li><a class="dropdown-item" href="#" data-estado="entregado">Entregado</a></li>
                        <li><a class="dropdown-item" href="#" data-estado="cancelado">Cancelado</a></li>
                    </ul>
                </div>
                <button class="btn btn-custom" id="actListadoPedidos"><i class="fas fa-sync-alt me-2"></i>Recargar</button>
                <button class="btn btn-custom" id="exportarPDF"><i class="fas fa-file-pdf me-2"></i>Exportar PDF</button>
                <button class="btn btn-custom" id="exportarExcel"><i class="fas fa-file-excel me-2"></i>Exportar Excel</button>
                <button class="btn btn-custom" id="btnAgregarPedido"><i class="fas fa-plus me-2"></i>Nuevo Pedido</button>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-0">
                <div id="tablaPedidos"></div>
            </div>
        </div>

        <!-- Modal Detalles Pedido -->
        <div class="modal fade" id="modalDetallesPedido" tabindex="-1" aria-labelledby="modalDetallesPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>ID Pedido:</strong>
                                <p id="detalleID"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Cliente:</strong>
                                <p id="detalleCliente"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Repartidor:</strong>
                                <p id="detalleRepartidor"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Establecimiento:</strong>
                                <p id="detalleEstablecimiento"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Dirección:</strong>
                                <p id="detalleDireccion"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha:</strong>
                                <p id="detalleFecha"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong>
                                <p id="detalleEstado"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Total:</strong>
                                <p id="detalleTotal"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tiempo Estimado:</strong>
                                <p id="detalleTiempoEstimado"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tiempo Real:</strong>
                                <p id="detalleTiempoReal"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Método de Pago:</strong>
                                <p id="detalleMetodoPago"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Costo de Envío:</strong>
                                <p id="detalleCostoEnvio"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Teléfono de Confirmación:</strong>
                                <p id="detalleTelefono"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Comprobante de Pago:</strong>
                                <p id="detalleComprobante"></p>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Notas:</strong>
                                <p id="detalleNotas"></p>
                            </div>
                            <div class="col-12">
                                <h6>Productos</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detalleProductos"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Pedido -->
        <div class="modal fade" id="modalEditarPedido" tabindex="-1" aria-labelledby="modalEditarPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarPedido" class="row g-3">
                            <input type="hidden" id="editarIDPedido" name="editarIDPedido">
                            <div class="col-md-6">
                                <label for="editarEstado" class="form-label">Estado*</label>
                                <select class="form-control" id="editarEstado" name="editarEstado" required>
                                    <option value="">Seleccione un estado</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="proceso">En proceso</option>
                                    <option value="camino">en camino</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un estado</div>
                            </div>
                            <div class="col-md-6">
                                <label for="editarRepartidor" class="form-label">Repartidor*</label>
                                <select class="form-control" id="editarRepartidor" name="editarRepartidor" required>
                                    <option value="">Seleccione un repartidor</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un repartidor</div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnEditarPedido">
                                    <i class="fas fa-save me-1"></i> Guardar Cambios
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Nuevo Pedido -->
        <div class="modal fade" id="modalNuevoPedido" tabindex="-1" aria-labelledby="modalNuevoPedidoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNuevoPedido" class="row g-3">
                            <div class="col-md-6">
                                <label for="id_usuario" class="form-label">Cliente*</label>
                                <select class="form-control" id="id_usuario" name="id_usuario" required>
                                    <option value="">Seleccione un cliente</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un cliente</div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_establecimiento" class="form-label">Establecimiento*</label>
                                <select class="form-control" id="id_establecimiento" name="id_establecimiento" required>
                                    <option value="">Seleccione un establecimiento</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un establecimiento</div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_direccion" class="form-label">Dirección*</label>
                                <select class="form-control" id="id_direccion" name="id_direccion" required>
                                    <option value="">Seleccione una dirección</option>
                                </select>
                                <div class="invalid-feedback">Seleccione una dirección</div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_metodo_pago" class="form-label">Método de Pago*</label>
                                <select class="form-control" id="id_metodo_pago" name="id_metodo_pago" required>
                                    <option value="">Seleccione un método</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un método de pago</div>
                            </div>
                            <div class="col-md-6">
                                <label for="costo_envio" class="form-label">Costo de Envío*</label>
                                <input type="number" step="0.01" class="form-control" id="costo_envio" name="costo_envio" required>
                                <div class="invalid-feedback">Ingrese un costo de envío válido</div>
                            </div>
                            <div class="col-md-6">
                                <label for="tiempo_estimado" class="form-label">Tiempo Estimado (HH:MM)*</label>
                                <input type="time" class="form-control" id="tiempo_estimado" name="tiempo_estimado" required>
                                <div class="invalid-feedback">Ingrese un tiempo estimado válido</div>
                            </div>
                            <div class="col-md-6">
                                <label for="total_calculado" class="form-label">Total Calculado</label>
                                <input type="text" class="form-control" id="total_calculado" readonly>
                            </div>
                            <div class="col-12">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea class="form-control" id="notas" name="notas" rows="4"></textarea>
                            </div>
                            <div class="col-12">
                                <h6>Productos</h6>
                                <div id="listaProductos" class="mb-3"></div>
                                <button type="button" class="btn btn-custom" id="agregarProducto"><i class="fas fa-plus me-1"></i> Agregar Producto</button>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnGuardarPedido">
                                    <i class="fas fa-save me-1"></i> Guardar Pedido
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script src="<?= URL_BASE ?>js/FuncionesAdmin/funcionesPedido.js?v=897"></script>
</body>

</html>