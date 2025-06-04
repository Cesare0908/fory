<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/productos.css">
</head>
<body class="container-fluid">
    <main class="main-container">
        <div class="header-container">
            <h1><i class="fas fa-boxes me-2"></i>GESTIÓN DE PRODUCTOS</h1>
            <div class="button-group">
                <button class="btn btn-custom" id="actListadoProductos"><i class="fas fa-sync-alt me-2"></i>Recargar</button>
                <button class="btn btn-custom" id="btnAgregarProducto"><i class="fas fa-plus me-2"></i>Añadir</button>
                <div class="dropdown">
                    <button class="btn btn-custom dropdown-toggle" type="button" id="dropdownExport" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download me-2"></i>Exportar
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownExport">
                        <li><a class="dropdown-item" href="http://localhost/fory-final/consultas/informePdfProductos.php"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="http://localhost/fory-final/consultas/informeExcelProductos.php"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="http://localhost/fory-final/consultas/informecsvProductos.php"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    </ul>
                </div>
                <button class="btn btn-custom" id="grafica"><i class="fas fa-chart-pie me-2"></i>Gráfica</button>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-0">
                <div id="tablaProductos"></div>
            </div>
        </div>

        <!-- Modal Nuevo Producto -->
        <div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-labelledby="modalNuevoProductoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNuevoProducto" class="row g-3">
                            <input type="hidden" class="form-control" id="id_producto" name="id_producto">
                            <div class="col-md-6">
                                <label for="nombre_producto" class="form-label">Nombre del Producto*</label>
                                <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
                                <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                            </div>
                            <div class="col-md-6">
                                <label for="precio" class="form-label">Precio*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback">Ingrese un precio válido (mayor que 0)</div>
                            </div>
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción*</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required></textarea>
                                <div class="invalid-feedback">La descripción debe tener al menos 10 caracteres</div>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock*</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                <div class="invalid-feedback">Ingrese una cantidad válida (0 o más)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="disponibilidad" class="form-label">Disponibilidad*</label>
                                <select class="form-control" id="disponibilidad" name="disponibilidad" required>
                                    <option value="disponible">Disponible</option>
                                    <option value="no disponible">No Disponible</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="imagen" class="form-label">Imagen</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                <div class="invalid-feedback">Por favor, suba una imagen válida (opcional)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="tamano_porcion" class="form-label">Tamaño/Porción (ejemplo "600 ml")</label>
                                <input type="text" class="form-control" id="tamano_porcion" name="tamano_porcion">
                            </div>
                            <div class="col-md-6">
                                <label for="id_categoria" class="form-label">Categoría*</label>
                                <select class="form-control" id="id_categoria" name="id_categoria" required>
                                    <option value="">Cargando categorías...</option>
                                </select>
                                <div class="invalid-feedback">Seleccione una categoría</div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnGuardarProducto">
                                    <i class="fas fa-save me-1"></i> Guardar Producto
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

        <!-- Modal Editar Producto -->
        <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarProducto" class="row g-3">
                            <input type="hidden" class="form-control" id="editarIDProducto" name="editarIDProducto">
                            <div class="col-md-6">
                                <label for="editarNombreProducto" class="form-label">Nombre del Producto*</label>
                                <input type="text" class="form-control" id="editarNombreProducto" name="editarNombreProducto" required>
                                <div class="invalid-feedback">El nombre debe tener al menos 3 caracteres</div>
                            </div>
                            <div class="col-md-6">
                                <label for="editarPrecio" class="form-label">Precio*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="editarPrecio" name="editarPrecio" step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback">Ingrese un precio válido (mayor que 0)</div>
                            </div>
                            <div class="col-12">
                                <label for="editarDescripcion" class="form-label">Descripción*</label>
                                <textarea class="form-control" id="editarDescripcion" name="editarDescripcion" rows="2" required></textarea>
                                <div class="invalid-feedback">La descripción debe tener al menos 10 caracteres</div>
                            </div>
                            <div class="col-md-6">
                                <label for="editarStock" class="form-label">Stock*</label>
                                <input type="number" class="form-control" id="editarStock" name="editarStock" min="0" required>
                                <div class="invalid-feedback">Ingrese una cantidad válida (0 o más)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="editarDisponibilidad" class="form-label">Disponibilidad*</label>
                                <select class="form-control" id="editarDisponibilidad" name="editarDisponibilidad" required>
                                    <option value="disponible">Disponible</option>
                                    <option value="no disponible">No Disponible</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editarImagen" class="form-label">Imagen</label>
                                <input type="file" class="form-control" id="editarImagen" name="editarImagen" accept="image/*">
                                <div class="invalid-feedback">Por favor, suba una imagen válida (opcional)</div>
                                <small class="form-text text-muted">Deje en blanco para mantener la imagen actual.</small>
                                <div id="imagenActualPreview" class="mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="editarTamanoPorcion" class="form-label">Tamaño/Porción</label>
                                <input type="text" class="form-control" id="editarTamanoPorcion" name="editarTamanoPorcion">
                            </div>
                            <div class="col-md-6">
                                <label for="editarCategoria" class="form-label">Categoría*</label>
                                <select class="form-control" id="editarCategoria" name="editarCategoria" required>
                                    <option value="">Cargando categorías...</option>
                                </select>
                                <div class="invalid-feedback">Seleccione una categoría</div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary me-2" id="btnEditarProducto">
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

        <!-- Modal Detalles Producto -->
        <div class="modal fade" id="modalDetallesProducto" tabindex="-1" aria-labelledby="modalDetallesProductoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="detalles-producto">
                            <p><strong>ID:</strong> <span id="detalleID"></span></p>
                            <p><strong>Nombre:</strong> <span id="detalleNombre"></span></p>
                            <p><strong>Descripción:</strong> <span id="detalleDescripcion"></span></p>
                            <p><strong>Precio:</strong> <span id="detallePrecio"></span></p>
                            <p><strong>Stock:</strong> <span id="detalleStock"></span></p>
                            <p><strong>Disponibilidad:</strong> <span id="detalleDisponibilidad"></span></p>
                            <p><strong>Imagen:</strong> <span id="detalleImagen"></span></p>
                            <p><strong>Tamaño/Porción:</strong> <span id="detalleTamanoPorcion"></span></p>
                            <p><strong>Categoría:</strong> <span id="detalleCategoria"></span></p>
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

        <!-- Modal Gráfica -->
        <div class="modal fade" id="modalGrafica" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-chart-pie me-2"></i>Gráfica de Productos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <canvas id="graficaCanvas" width="400" height="200"></canvas>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js"></script>
    <script src="../js/funcionesProducto.js?v=33333"></script>
</body>
</html>