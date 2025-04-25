<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - FORY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../css/DISEÑOSCLIENTE/editarPerfil.css?v=5">
</head>
<body class="container-fluid">
    <div class="login-box mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user me-2"></i> Mi Perfil</h2>
            <a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="btn btn-custom-primary">
                <i class="fas fa-arrow-left me-1"></i> Ir a la pagina principal
            </a>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>ID:</strong>
                <p id="perfilID"></p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Nombre:</strong>
                <p id="perfilNombre"></p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Apellido Paterno:</strong>
                <p id="perfilApPaterno"></p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Apellido Materno:</strong>
                <p id="perfilApMaterno"></p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Correo:</strong>
                <p id="perfilCorreo"></p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Teléfono:</strong>
                <p id="perfilTelefono"></p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Rol:</strong>
                <p id="perfilRol"></p>
            </div>
            <div class="col-12 mb-3">
                <button class="btn btn-custom-warning w-100" id="editarPerfilBtn">
                    <i class="fas fa-edit me-1"></i> Editar Perfil
                </button>
            </div>
            <div class="col-12 mb-3">
                <h4>Mis Direcciones</h4>
                <div id="listaDirecciones"></div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPerfil" class="row g-3">
                        <input type="hidden" id="editarIDUsuario" name="editarIDUsuario">
                        <div class="col-md-6">
                            <label for="editarNombre" class="form-label">Nombre*</label>
                            <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                            <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarApPaterno" class="form-label">Apellido Paterno*</label>
                            <input type="text" class="form-control" id="editarApPaterno" name="ap_paterno" required>
                            <div class="invalid-feedback">El apellido paterno debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarApMaterno" class="form-label">Apellido Materno*</label>
                            <input type="text" class="form-control" id="editarApMaterno" name="ap_materno" required>
                            <div class="invalid-feedback">El apellido materno debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarCorreo" class="form-label">Correo*</label>
                            <input type="email" class="form-control" id="editarCorreo" name="correo" required>
                            <div class="invalid-feedback">Ingrese un correo válido</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarTelefono" class="form-label">Teléfono*</label>
                            <input type="text" class="form-control" id="editarTelefono" name="telefono" required>
                            <div class="invalid-feedback">Ingrese un teléfono válido (10 dígitos)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarContrasena" class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" class="form-control" id="editarContrasena" name="contrasena">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmarContrasena" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirmarContrasena" name="confirmarContrasena">
                            <div class="invalid-feedback">Las contraseñas no coinciden</div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary me-2" id="btnGuardarCambios">
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

    <!-- Modal Editar Dirección -->
    <div class="modal fade" id="modalEditarDireccion" tabindex="-1" aria-labelledby="modalEditarDireccionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-marker-alt me-2"></i> Editar Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarDireccion" class="row g-3">
                        <input type="hidden" id="editarIdDireccion" name="id_direccion">
                        <div class="col-md-6">
                            <label for="editarCalle" class="form-label">Calle*</label>
                            <input type="text" class="form-control" id="editarCalle" name="calle" required>
                            <div class="invalid-feedback">La calle debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarNumero" class="form-label">Número*</label>
                            <input type="text" class="form-control" id="editarNumero" name="numero" required>
                            <div class="invalid-feedback">Ingrese un número válido</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarColonia" class="form-label">Colonia*</label>
                            <input type="text" class="form-control" id="editarColonia" name="colonia" required>
                            <div class="invalid-feedback">La colonia debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarCiudad" class="form-label">Ciudad*</label>
                            <input type="text" class="form-control" id="editarCiudad" name="ciudad" required>
                            <div class="invalid-feedback">La ciudad debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarEstado" class="form-label">Estado*</label>
                            <input type="text" class="form-control" id="editarEstado" name="estado" required>
                            <div class="invalid-feedback">El estado debe tener al menos 3 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="editarCodigoPostal" class="form-label">Código Postal*</label>
                            <input type="text" class="form-control" id="editarCodigoPostal" name="codigo_postal" required>
                            <div class="invalid-feedback">El código postal debe tener 5 dígitos</div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary me-2" id="btnGuardarDireccion">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/FuncionesCliente/funcionesEditarPerfil.js"></script>
</body>
</html>