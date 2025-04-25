<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/productos.css">
</head>

<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-users me-2"></i>GESTIÓN DE USUARIOS</h1>
            <div class="button-group">
                <button class="btn btn-custom" id="actListadoUsuarios"><i class="fas fa-sync-alt me-2"></i>Recargar</button>
                <button class="btn btn-custom" id="btnAgregarUsuario"><i class="fas fa-plus me-2"></i>Añadir</button>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-0">
                <div id="tablaUsuarios"></div>
            </div>
        </div>

        <!-- Modal Detalles Usuario -->
        <div class="modal fade" id="modalDetallesUsuario" tabindex="-1" aria-labelledby="modalDetallesUsuarioLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>ID:</strong>
                                <p id="detalleID"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Nombre:</strong>
                                <p id="detalleNombre"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Apellido Paterno:</strong>
                                <p id="detalleApPaterno"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Apellido Materno:</strong>
                                <p id="detalleApMaterno"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Correo:</strong>
                                <p id="detalleCorreo"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Teléfono:</strong>
                                <p id="detalleTelefono"></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Rol:</strong>
                                <p id="detalleRol"></p>
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

        <!-- Modal Nuevo Usuario -->
        <div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNuevoUsuario" class="row g-3">
                            <input type="hidden" class="form-control" id="id_usuario" name="id_usuario">

                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="ap_paterno" class="form-label">Apellido Paterno*</label>
                                <input type="text" class="form-control" id="ap_paterno" name="ap_paterno" required>
                                <div class="invalid-feedback">El apellido paterno debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="ap_materno" class="form-label">Apellido Materno*</label>
                                <input type="text" class="form-control" id="ap_materno" name="ap_materno" required>
                                <div class="invalid-feedback">El apellido materno debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="correo" class="form-label">Correo*</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                                <div class="invalid-feedback">Ingrese un correo válido</div>
                            </div>

                            <div class="col-md-6">
                                <label for="contraseña" class="form-label">Contraseña*</label>
                                <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                                <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono*</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" required>
                                <div class="invalid-feedback">Ingrese un teléfono válido (10 dígitos)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="id_rol" class="form-label">Rol*</label>
                                <select class="form-control" id="id_rol" name="id_rol" required>
                                    <option value="">Seleccione un rol</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un rol</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnGuardarUsuario">
                                    <i class="fas fa-save me-1"></i> Guardar Usuario
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

        <!-- Modal Editar Usuario -->
        <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarUsuario" class="row g-3">
                            <input type="hidden" class="form-control" id="editarIDUsuario" name="editarIDUsuario">

                            <div class="col-md-6">
                                <label for="editarNombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="editarNombre" name="editarNombre" required>
                                <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="editarApPaterno" class="form-label">Apellido Paterno*</label>
                                <input type="text" class="form-control" id="editarApPaterno" name="editarApPaterno" required>
                                <div class="invalid-feedback">El apellido paterno debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="editarApMaterno" class="form-label">Apellido Materno*</label>
                                <input type="text" class="form-control" id="editarApMaterno" name="editarApMaterno" required>
                                <div class="invalid-feedback">El apellido materno debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="editarCorreo" class="form-label">Correo*</label>
                                <input type="email" class="form-control" id="editarCorreo" name="editarCorreo" required>
                                <div class="invalid-feedback">Ingrese un correo válido</div>
                            </div>

                            <div class="col-md-6">
                                <label for="editarTelefono" class="form-label">Teléfono*</label>
                                <input type="text" class="form-control" id="editarTelefono" name="editarTelefono" required>
                                <div class="invalid-feedback">Ingrese un teléfono válido (10 dígitos)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="editarRol" class="form-label">Rol*</label>
                                <select class="form-control" id="editarRol" name="editarRol" required>
                                    <option value="">Seleccione un rol</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un rol</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnEditarUsuario">
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script src="../js/funcionesUsuario.js?v=4"></script>
</body>

</html>