<?php include '../config.php'; ?>

<!DOCTYPE html>
<html lang="es">  
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Repartidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE ?>css/repartidores.css">
</head>
<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-truck me-2"></i>Gestión de Repartidores</h1>
            <div class="button-group">
                <button class="btn btn-custom" id="actListadoRepartidores"><i class="fas fa-sync-alt me-2"></i>Recargar</button>
                <button class="btn btn-custom" id="btnAgregarRepartidor"><i class="fas fa-plus me-2"></i>Añadir</button>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-0">
                <div id="tablaRepartidores"></div>
            </div>
        </div>

        <!-- Modal Nuevo Repartidor -->
        <div class="modal fade" id="modalNuevoRepartidor" tabindex="-1" aria-labelledby="modalNuevoRepartidorLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Repartidor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNuevoRepartidor" class="row g-3">
                            <div class="col-md-6">
                                <label for="id_usuario" class="form-label">Usuario*</label>
                                <select class="form-control" id="id_usuario" name="id_usuario" required>
                                    <option value="">Seleccione un usuario</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un usuario</div>
                            </div>

                            <div class="col-md-6">
                                <label for="disponibilidad" class="form-label">Disponibilidad*</label>
                                <select class="form-control" id="disponibilidad" name="disponibilidad" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Disponible">Disponible</option>
                                    <option value="No disponible">No disponible</option>
                                </select>
                                <div class="invalid-feedback">Seleccione la disponibilidad</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnGuardarRepartidor">
                                    <i class="fas fa-save me-1"></i> Guardar Repartidor
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

        <!-- Modal Editar Repartidor -->
        <div class="modal fade" id="modalEditarRepartidor" tabindex="-1" aria-labelledby="modalEditarRepartidorLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Repartidor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarRepartidor" class="row g-3">
                            <input type="hidden" id="editarIDRepartidor" name="editarIDRepartidor">
                            <div class="col-md-6">
                                <label for="editarUsuario" class="form-label">Usuario*</label>
                                <select class="form-control" id="editarUsuario" name="editarUsuario" required>
                                    <option value="">Seleccione un usuario</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un usuario</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnEditarRepartidor">
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

        <!-- Modal Detalles Vehículo -->
        <div class="modal fade" id="modalDetallesVehiculo" tabindex="-1" aria-labelledby="modalDetallesVehiculoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-car me-2"></i>Detalles del Vehículo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="detalles-vehiculo">
                            <p><strong>Tipo de Vehículo:</strong> <span id="detalleTipoVehiculo"></span></p>
                            <p><strong>Marca:</strong> <span id="detalleMarca"></span></p>
                            <p><strong>Modelo:</strong> <span id="detalleModelo"></span></p>
                            <p><strong>Color:</strong> <span id="detalleColor"></span></p>
                            <p><strong>Placas:</strong> <span id="detallePlacas"></span></p>
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script src="<?= URL_BASE ?>js/FuncionesAdmin/funcionesRepartidor.js?v=5"></script>
</body>
</html>