<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto - FORY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../../css/DISEÑOSCLIENTE/detalle_producto.css?v=4">
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="h4 mb-0"><a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="text-white text-decoration-none">FORY</a></span>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-2" id="ver-carrito"><i class="fas fa-shopping-cart me-2"></i>Ver Carrito (<span id="cart-counter">0</span>)</button>
                <a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="btn btn-outline-light"><i class="fas fa-arrow-left me-2"></i>Regresar</a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 pt-5">
        <div class="product-detail-container">
            <div class="loading-spinner text-center" id="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-2">Cargando producto...</p>
            </div>
            <div class="row product-detail-content" id="product-detail-content" style="display: none;">
                <div class="col-lg-6 mb-4 d-flex justify-content-center">
                    <div class="product-image-container">
                        <img id="product-image" class="img-fluid product-image" alt="Producto">
                    </div>
                </div>
                <div class="col-lg-6 product-info">
                    <h1 id="product-name" class="product-title"></h1>
                    <p id="product-category" class="category-text"></p>
                    <div class="price-container">
                        <span id="product-price" class="price"></span>
                    </div>
                    <p id="product-description" class="product-description"></p>
                    <p id="product-stock" class="stock-info"></p>
                    <div class="action-buttons d-flex align-items-center">
                        <input type="number" id="cantidad-producto" class="form-control me-3 quantity-input" value="1" min="1" style="width: 100px;">
                        <button class="btn btn-custom-blue agregar-carrito" id="add-to-cart-btn">
                            <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                        </button>
                    </div>
                    <div class="additional-info">
                        <h5 class="section-title">Detalles del Producto</h5>
                        <ul>
                            <li><strong>Disponibilidad:</strong> <span id="product-availability"></span></li>
                            <li><strong>Categoría:</strong> <span id="product-category-detail"></span></li>
                            <li><strong>Tienda:</strong> Tradicional 45</li>
                            <li><strong>Costo de envío:</strong> $30.00 (por pedido)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Sección de Productos Relacionados -->
            <div class="related-products mt-5" id="related-products" style="display: none;">
                <h3 class="related-products-title">Productos Relacionados</h3>
                <div class="row" id="related-products-list"></div>
            </div>
        </div>
    </div>

    <!-- Sidebar del Carrito -->
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Mi Carrito</h5>
            <button class="btn btn-close" id="cerrar-carrito-x"></button>
        </div>
        <div class="cart-body" id="carrito-body"></div>
        <div class="cart-footer">
            <p class="mb-2">Subtotal: $<span id="subtotal-carrito">0.00</span></p>
            <p class="mb-3">Total (con envío): $<span id="total-carrito">0.00</span></p>
            <button class="btn btn-primary w-100 mb-2" id="continuar-pedido" disabled>Continuar Pedido</button>
            <button class="btn btn-secondary w-100" id="cerrar-carrito-footer">Cerrar</button>
        </div>
    </div>

    <!-- Modal Dirección -->
    <div class="modal fade" id="modal-direccion" tabindex="-1" aria-labelledby="modal-direccion-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-direccion-label">Selecciona tu Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección de envío</label>
                        <select class="form-select" id="direccion">
                            <option value="">Selecciona una dirección</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" id="agregar-direccion">Agregar Nueva Dirección</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="continuar-pago">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Dirección -->
    <div class="modal fade" id="modal-nueva-direccion" tabindex="-1" aria-labelledby="modal-nueva-direccion-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-nueva-direccion-label">Nueva Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-nueva-direccion" novalidate>
                        <div class="mb-3">
                            <label for="calle" class="form-label">Calle *</label>
                            <input type="text" class="form-control" id="calle" required>
                            <div class="invalid-feedback">Por favor, ingresa la calle.</div>
                        </div>
                        <div class="mb-3">
                            <label for="numero" class="form-label">Número *</label>
                            <input type="text" class="form-control" id="numero" required>
                            <div class="invalid-feedback">Por favor, ingresa el número.</div>
                        </div>
                        <div class="mb-3">
                            <label for="colonia" class="form-label">Colonia *</label>
                            <input type="text" class="form-control" id="colonia" required>
                            <div class="invalid-feedback">Por favor, ingresa la colonia.</div>
                        </div>
                        <div class="mb-3">
                            <label for="ciudad" class="form-label">Ciudad *</label>
                            <input type="text" class="form-control" id="ciudad" required>
                            <div class="invalid-feedback">Por favor, ingresa la ciudad.</div>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <input type="text" class="form-control" id="estado" required>
                            <div class="invalid-feedback">Por favor, ingresa el estado.</div>
                        </div>
                        <div class="mb-3">
                            <label for="codigo_postal" class="form-label">Código Postal *</label>
                            <input type="text" class="form-control" id="codigo_postal" required pattern="[0-9]{5}">
                            <div class="invalid-feedback">El código postal debe ser un número de 5 dígitos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="referencias" class="form-label">Referencias</label>
                            <textarea class="form-control" id="referencias"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Dirección</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Método de Pago -->
    <div class="modal fade" id="modal-pago" tabindex="-1" aria-labelledby="modal-pago-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-pago-label">Método de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-pago" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="metodo-pago" class="form-label">Selecciona un método de pago *</label>
                            <select class="form-select" id="metodo-pago" name="metodo_pago" required>
                                <option value="">Selecciona un método</option>
                            </select>
                            <div class="invalid-feedback">Por favor selecciona un método de pago.</div>
                        </div>
                        <!-- Información para transferencia -->
                        <div class="mb-3 alert alert-info" id="transferencia-info" style="display: none;">
                            <p><strong>Cuenta para transferencia:</strong> 4152 3137 3243 4019</p>
                            <p>Por favor, realiza la transferencia y sube el comprobante a continuación.</p>
                        </div>
                        <!-- Campo para número telefónico (visible solo para Efectivo) -->
                        <div class="mb-3" id="telefono-container" style="display: none;">
                            <label for="telefono-confirmacion" class="form-label">Número Telefónico * (para confirmar entrega)</label>
                            <input type="tel" class="form-control" id="telefono-confirmacion" name="telefono_confirmacion" pattern="[0-9]{10}" placeholder="Ej: 1234567890" required>
                            <div class="invalid-feedback">Ingresa un número de 10 dígitos.</div>
                        </div>
                        <!-- Campo para cargar comprobante (visible solo para Transferencia) -->
                        <div class="mb-3" id="comprobante-container" style="display: none;">
                            <label for="comprobante-pago" class="form-label">Comprobante de Pago * (JPEG, PNG o PDF, máx. 5MB)</label>
                            <input type="file" class="form-control" id="comprobante-pago" name="comprobante_pago" accept="image/jpeg,image/png,application/pdf" required>
                            <div class="invalid-feedback">Por favor sube un archivo válido.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Confirmar Pedido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="http://localhost/fory-final/js/FuncionesCliente/detalle_producto.js?v=5"></script>
</body>
</html>