
<?php include '../config.php'; ?> 
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE ?>css/productos.css">
</head>

<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-list me-2"></i>GESTIÓN DE CATEGORÍAS</h1>
            <div class="button-group">
                <button class="btn btn-custom" id="actListadoCategorias"><i class="fas fa-sync-alt me-2"></i>Recargar</button>
                <button class="btn btn-custom" id="btnAgregarCategoria"><i class="fas fa-plus me-2"></i>Añadir</button>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-0">
                <div id="tablaCategorias"></div>
            </div>
        </div>

        <!-- Modal Nueva Categoría -->
        <div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-labelledby="modalNuevaCategoriaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Agregar Nueva Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNuevaCategoria" class="row g-3">
                            <input type="hidden" class="form-control" id="id_categoria" name="id_categoria">

                            <div class="col-md-6">
                                <label for="nombre_categoria" class="form-label">Nombre de la Categoría*</label>
                                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
                                <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción*</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required></textarea>
                                <div class="invalid-feedback">La descripción debe tener al menos 10 caracteres</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnGuardarCategoria">
                                    <i class="fas fa-save me-1"></i> Guardar Categoría
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

        <!-- Modal Editar Categoría -->
        <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarCategoria" class="row g-3">
                            <input type="hidden" class="form-control" id="editarIDCategoria" name="editarIDCategoria">

                            <div class="col-md-6">
                                <label for="editarNombreCategoria" class="form-label">Nombre de la Categoría*</label>
                                <input type="text" class="form-control" id="editarNombreCategoria" name="editarNombreCategoria" required>
                                <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                            </div>

                            <div class="col-12">
                                <label for="editarDescripcion" class="form-label">Descripción*</label>
                                <textarea class="form-control" id="editarDescripcion" name="editarDescripcion" rows="2" required></textarea>
                                <div class="invalid-feedback">La descripción debe tener al menos 10 caracteres</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnEditarCategoria">
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
    <script src="<?= URL_BASE ?>js/FuncionesAdmin/funcionesCategoria.js?v=2"></script>
</body>

</html>