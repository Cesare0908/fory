<!DOCTYPE html>
<html lang="es">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - FORY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="http://localhost/fory-final/css/DISEÑOSCLIENTE/pedidos.css?v=334">
</head>
<body>
    <!-- Botón flotante del chatbot con mensaje -->
    <div class="chatbot-floating-container">
        <span class="chatbot-help-message">¡Necesitas ayuda?</span>
        <button class="chatbot-floating" data-bs-toggle="modal" data-bs-target="#chatbotModal">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>

    <!-- Menú lateral -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="text-white text-decoration-none">FORY</a></h4>
            <button class="btn-close btn-warning" id="cerrar-sidebar" aria-label="Cerrar menú">X</button>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/fory-final/php/ModuloCliente/EditarPerfil.php"><i class="fas fa-user me-2"></i> Administrar Perfil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/fory-final/php/ModuloCliente/historial_compras.php"><i class="fas fa-history me-2"></i> Historial de Compras</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/fory-final/php/salir.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Encabezado -->
    <header class="bg-dark text-white py-3 fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-light me-2" id="abrir-sidebar"><i class="fas fa-bars"></i></button>
                <span class="h4 mb-0"><a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="text-white text-decoration-none">FORY</a></span>
            </div>
            <div class="input-group w-25">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="buscador" class="form-control" placeholder="Busca productos...">
            </div>
            <div class="d-flex align-items-center">
                <div class="dropdown me-3">
                    <a href="#" class="text-white user-icon" id="user-menu" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="http://localhost/fory-final/php/ModuloCliente/EditarPerfil.php">Ver Perfil</a></li>
                        <li><a class="dropdown-item" href="http://localhost/fory-final/php/salir.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
                <a href="#" id="ver-carrito" class="text-white position-relative cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-counter" class="badge bg-danger rounded-circle position-absolute">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Ícono de carrito flotante -->
    <button class="floating-cart cart-icon" id="floating-cart">
        <i class="fas fa-shopping-cart"></i>
        <span id="floating-cart-counter" class="badge bg-danger rounded-circle">0</span>
    </button>

    <div class="container mt-5 pt-5">
        <!-- Selección de tienda -->
        <div class="mb-4 text-center">
            <h3 class="store-title">TRADICIONAL 45</h3>
            <input type="hidden" id="establecimiento" value="1">
        </div>

        <!-- Carrusel de categorías -->
        <div class="mb-4 category-carousel-container">
            <h3>Categorías</h3>
            <div class="category-carousel" id="category-carousel">
                <!-- Categorías cargadas dinámicamente -->
            </div>
            <button class="carousel-control-prev" data-target="category-carousel"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control-next" data-target="category-carousel"><i class="fas fa-chevron-right"></i></button>
        </div>

        <!-- Listado de productos -->
        <div class="mb-5 product-carousel-container" id="lista-productos-container">
            <h3 id="lista-productos-title">Explora el Menú</h3>
            <div class="product-carousel" id="lista-productos">
                <!-- Productos cargados dinámicamente -->
            </div>
            <button class="carousel-control-prev" data-target="lista-productos"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control-next" data-target="lista-productos"><i class="fas fa-chevron-right"></i></button>
        </div>

        <!-- Productos relacionados -->
        <div class="mb-5 product-carousel-container" id="productos-relacionados-container" style="display: none;">
            <h3>Productos Relacionados</h3>
            <div class="product-carousel" id="productos-relacionados">
                <!-- Productos relacionados cargados dinámicamente -->
            </div>
            <button class="carousel-control-prev" data-target="productos-relacionados"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control-next" data-target="productos-relacionados"><i class="fas fa-chevron-right"></i></button>
        </div>

        <!-- Productos recomendados -->
        <div class="mb-5 product-carousel-container" id="productos-recomendados-container">
            <h3>Recomendados para Ti</h3>
            <div class="product-carousel" id="productos-recomendados">
                <!-- Productos recomendados cargados dinámicamente -->
            </div>
            <button class="carousel-control-prev" data-target="productos-recomendados"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control-next" data-target="productos-recomendados"><i class="fas fa-chevron-right"></i></button>
        </div>

        <!-- Productos más vendidos -->
        <div class="mb-5 product-carousel-container" id="productos-mas-vendidos-container">
            <h3>Más Vendidos</h3>
            <div class="product-carousel" id="productos-mas-vendidos">
                <!-- Productos cargados dinámicamente -->
            </div>
            <button class="carousel-control-prev" data-target="productos-mas-vendidos"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control-next" data-target="productos-mas-vendidos"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <!-- Carrito -->
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h4><i class="fas fa-shopping-cart me-2"></i> Tu Carrito</h4>
            <div>
                <button class="btn btn-outline-secondary btn-sm me-2" id="ocultar-carrito">Ocultar</button>
                <button class="btn btn-danger btn-sm me-2" id="cerrar-carrito">Cerrar</button>
                <button class="btn btn-close btn-close-white" id="cerrar-carrito-x" aria-label="Cerrar carrito"></button>
            </div>
        </div>
        <div class="cart-body" id="carrito-body">
            <!-- Ítems del carrito -->
        </div>
        <div class="cart-footer">
            <h5>Subtotal: $<span id="subtotal-carrito">0.00</span></h5>
            <h5>Costo de Envío (por pedido): $30.00</h5>
            <h5>Total: $<span id="total-carrito">0.00</span></h5>
            <button class="btn btn-primary w-100 mb-2" id="continuar-pedido" disabled>Continuar Pedido</button>
            <button class="btn btn-danger w-100" id="cerrar-carrito-footer">Cerrar Carrito</button>
        </div>
    </div>

    <!-- Modal para seleccionar dirección -->
    <div class="modal fade" id="modal-direccion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección de Entrega:</label>
                        <select id="direccion" name="direccion" class="form-select" required>
                            <option value="">Selecciona una dirección</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" id="agregar-direccion">Agregar Nueva Dirección</button>
                    <button class="btn btn-success w-100 mt-2" id="continuar-pago">Continuar con Método de Pago</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar dirección -->
    <div class="modal fade" id="modal-nueva-direccion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nueva Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="form-nueva-direccion" novalidate>
                        <div class="mb-3">
                            <label for="calle" class="form-label">Calle *</label>
                            <input type="text" class="form-control" id="calle" name="calle" required>
                            <div class="invalid-feedback">La calle es obligatoria.</div>
                        </div>
                        <div class="mb-3">
                            <label for="numero" class="form-label">Número *</label>
                            <input type="text" class="form-control" id="numero" name="numero" required>
                            <div class="invalid-feedback">El número es obligatorio.</div>
                        </div>
                        <div class="mb-3">
                            <label for="colonia" class="form-label">Colonia *</label>
                            <input type="text" class="form-control" id="colonia" name="colonia" required>
                            <div class="invalid-feedback">La colonia es obligatoria.</div>
                        </div>
                        <div class="mb-3">
                            <label for="ciudad" class="form-label">Ciudad *</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                            <div class="invalid-feedback">La ciudad es obligatoria.</div>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <input type="text" class="form-control" id="estado" name="estado" required>
                            <div class="invalid-feedback">El estado es obligatorio.</div>
                        </div>
                        <div class="mb-3">
                            <label for="codigo_postal" class="form-label">Código Postal *</label>
                            <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required pattern="[0-9]{5}">
                            <div class="invalid-feedback">El código postal debe ser un número de 5 dígitos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="referencias" class="form-label">Referencias</label>
                            <textarea class="form-control" id="referencias" name="referencias"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Guardar Dirección</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para método de pago -->
    <div class="modal fade" id="modal-pago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Método de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="form-pago" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="metodo-pago" class="form-label">Método de Pago:</label>
                            <select id="metodo-pago" name="metodo_pago" class="form-select" required>
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
                        <button type="submit" class="btn btn-success w-100">Confirmar Pedido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal del chatbot -->
    <div class="modal fade chatbot-modal" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content chat-box">
                <div class="modal-header">
                    <h5 class="modal-title header-title" id="chatbotModalLabel">Chatbot de Fory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-container">
                        <div class="chat-messages" id="chatContainer">
                            <!-- Los mensajes se añadirán aquí dinámicamente -->
                        </div>
                        <div class="chat-input">
                            <input type="text" id="chatInput" class="form-control chat-input-message" placeholder="Escribe tu mensaje..." autocomplete="off">
                            <input type="number" id="presupuestoInput" class="form-control chat-input-budget" placeholder="Presupuesto (opcional)" min="0" step="0.01">
                            <button class="btn chat-input-btn" onclick="enviarMensaje()">Enviar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="http://localhost/fory-final/js/FuncionesCliente/pedidos.js?v=3433434"></script>
    <script src="http://localhost/fory-final/js/FuncionesCliente/FuncionesChat.js?v=1577"></script>
</body>
</html>  