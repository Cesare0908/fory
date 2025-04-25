document.addEventListener("DOMContentLoaded", function () {
    // Obtener el ID del producto desde la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idProducto = urlParams.get("id");

    const loadingSpinner = document.getElementById("loading-spinner");
    const productDetailContent = document.getElementById("product-detail-content");
    const productImage = document.getElementById("product-image");
    const productName = document.getElementById("product-name");
    const productCategory = document.getElementById("product-category");
    const productPrice = document.getElementById("product-price");
    const productDescription = document.getElementById("product-description");
    const productStock = document.getElementById("product-stock");
    const productAvailability = document.getElementById("product-availability");
    const productCategoryDetail = document.getElementById("product-category-detail");
    const addToCartBtn = document.getElementById("add-to-cart-btn");
    const cantidadProducto = document.getElementById("cantidad-producto");
    const cartSidebar = document.getElementById("cart-sidebar");
    const verCarrito = document.getElementById("ver-carrito");
    const cerrarCarritoX = document.getElementById("cerrar-carrito-x");
    const cerrarCarritoFooter = document.getElementById("cerrar-carrito-footer");
    const carritoBody = document.getElementById("carrito-body");
    const subtotalCarrito = document.getElementById("subtotal-carrito");
    const totalCarrito = document.getElementById("total-carrito");
    const cartCounter = document.getElementById("cart-counter");
    const relatedProducts = document.getElementById("related-products");
    const relatedProductsList = document.getElementById("related-products-list");
    const continuarBtn = document.getElementById("continuar-pedido");
    const direccionSelect = document.getElementById("direccion");
    const agregarDireccionBtn = document.getElementById("agregar-direccion");
    const continuarPagoBtn = document.getElementById("continuar-pago");
    const metodoPagoSelect = document.getElementById("metodo-pago");
    const transferenciaInfo = document.getElementById("transferencia-info");
    const telefonoContainer = document.getElementById("telefono-container");
    const comprobanteContainer = document.getElementById("comprobante-container");
    const formPago = document.getElementById("form-pago");
    const formNuevaDireccion = document.getElementById("form-nueva-direccion");

    // Cargar el carrito desde localStorage
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    let currentProductCategoryId = null;

    // Función para actualizar el carrito
    function actualizarCarrito() {
        carritoBody.innerHTML = "";
        let subtotal = 0;
        let itemCount = 0;

        if (carrito.length === 0) {
            carritoBody.innerHTML = '<p class="text-center text-muted">El carrito está vacío.</p>';
        } else {
            carrito.forEach(item => {
                const sub = item.precio * item.cantidad;
                subtotal += sub;
                itemCount += item.cantidad;
                const itemDiv = document.createElement("div");
                itemDiv.className = "cart-item d-flex align-items-center mb-3 animate__animated animate__fadeIn";
                itemDiv.innerHTML = `
                    <img src="${item.imagen}" class="cart-item-img me-3" alt="${item.nombre}">
                    <div class="flex-grow-1">
                        <h6>${item.nombre}</h6>
                        <p class="text-muted mb-1">$${item.precio} x ${item.cantidad}</p>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control cantidad-carrito w-25 me-2" 
                                   data-id="${item.id_producto}" value="${item.cantidad}" min="1" max="${item.stock}">
                            <button class="btn btn-danger btn-sm eliminar-carrito" data-id="${item.id_producto}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                carritoBody.appendChild(itemDiv);
            });
        }

        subtotalCarrito.textContent = subtotal.toFixed(2);
        totalCarrito.textContent = (subtotal + 30).toFixed(2);
        cartCounter.textContent = itemCount;
        continuarBtn.disabled = carrito.length === 0;
        localStorage.setItem("carrito", JSON.stringify(carrito));
    }

    // Función para cargar direcciones
    function cargarDirecciones() {
        direccionSelect.innerHTML = '<option value="">Selecciona una dirección</option>';
        fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarDirecciones")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.direcciones.forEach(dir => {
                        const option = document.createElement("option");
                        option.value = dir.id_direccion;
                        option.textContent = `${dir.calle} ${dir.numero}, ${dir.colonia}, ${dir.ciudad}`;
                        direccionSelect.appendChild(option);
                    });
                }
            });
    }

    // Función para cargar métodos de pago
    function cargarMetodosPago() {
        fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarMetodosPago")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.metodos.forEach(met => {
                        const option = document.createElement("option");
                        option.value = met.id_metodo_pago;
                        option.textContent = met.nombre_metodo;
                        metodoPagoSelect.appendChild(option);
                    });
                }
            });
    }

    // Función para cargar productos relacionados
    function cargarProductosRelacionados(idCategoria) {
        if (!idCategoria) return;

        fetch(`../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarProductosRelacionados&id_categoria=${idCategoria}&id_producto=${idProducto}`)
            .then(response => response.json())
            .then(data => {
                relatedProductsList.innerHTML = "";
                if (data.success && data.productos.length > 0) {
                    data.productos.forEach(prod => {
                        const card = document.createElement("div");
                        card.className = "col-md-3 col-sm-6 mb-4";
                        card.innerHTML = `
                            <div class="card product-card">
                                <img src="${prod.imagen}" class="card-img-top product-image-related" alt="${prod.nombre_producto}" data-id="${prod.id_producto}">
                                <div class="card-body">
                                    <h5 class="card-title product-name-related" data-id="${prod.id_producto}">${prod.nombre_producto}</h5>
                                    <p class="card-text text-muted mb-1">${prod.descripcion.slice(0, 30)}...</p>
                                    <p class="card-text price mb-2"><strong>$${prod.precio}</strong></p>
                                    <button class="btn btn-success agregar-carrito w-100" 
                                            data-id="${prod.id_producto}" 
                                            data-nombre="${prod.nombre_producto}" 
                                            data-precio="${prod.precio}" 
                                            data-stock="${prod.stock}"
                                            data-imagen="${prod.imagen}">Agregar</button>
                                </div>
                            </div>
                        `;
                        relatedProductsList.appendChild(card);
                    });
                    relatedProducts.style.display = "block";
                }
            })
            .catch(error => {
                console.error("Error al cargar productos relacionados:", error);
            });
    }

    // Cargar los detalles del producto
    if (idProducto) {
        fetch(`../../controladores/ControladorCliente/controlador_pedidos.php?ope=obtenerProducto&id_producto=${idProducto}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const producto = data.producto;
                    // Rellenar los campos
                    productImage.src = producto.imagen;
                    productImage.alt = producto.nombre_producto;
                    productName.textContent = producto.nombre_producto;
                    productCategory.textContent = `Categoría: ${producto.nombre_categoria}`;
                    productPrice.textContent = `$${producto.precio}`;
                    productDescription.textContent = producto.descripcion;
                    productStock.textContent = `Stock disponible: ${producto.stock} unidades`;
                    productAvailability.textContent = producto.stock > 0 ? "En stock" : "Sin stock";
                    productCategoryDetail.textContent = producto.nombre_categoria;

                    // Configurar el botón de agregar al carrito
                    addToCartBtn.dataset.id = producto.id_producto;
                    addToCartBtn.dataset.nombre = producto.nombre_producto;
                    addToCartBtn.dataset.precio = producto.precio;
                    addToCartBtn.dataset.stock = producto.stock;
                    addToCartBtn.dataset.imagen = producto.imagen;

                    // Configurar el selector de cantidad
                    cantidadProducto.max = producto.stock;
                    currentProductCategoryId = producto.id_categoria; // Guardar la categoría para productos relacionados

                    // Mostrar el contenido y ocultar el spinner
                    loadingSpinner.style.display = "none";
                    productDetailContent.style.display = "flex";

                    // Cargar productos relacionados al inicio
                    cargarProductosRelacionados(producto.id_categoria);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.mensaje || "No se encontró el producto",
                    }).then(() => {
                        window.location.href = "http://localhost/fory-final/php/ModuloCliente/cliente.php";
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error al cargar el producto",
                }).then(() => {
                    window.location.href = "http://localhost/fory-final/php/ModuloCliente/cliente.php";
                });
            });
    } else {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se especificó un producto",
        }).then(() => {
            window.location.href = "http://localhost/fory-final/php/ModuloCliente/cliente.php";
        });
    }

    // Agregar al carrito
    addToCartBtn.addEventListener("click", function () {
        const id = this.dataset.id;
        const nombre = this.dataset.nombre;
        const precio = parseFloat(this.dataset.precio);
        const stock = parseInt(this.dataset.stock);
        const imagen = this.dataset.imagen;
        const cantidad = parseInt(cantidadProducto.value);

        if (cantidad <= 0 || isNaN(cantidad)) {
            Swal.fire("Error", "Por favor, selecciona una cantidad válida", "error");
            return;
        }

        if (cantidad > stock) {
            Swal.fire("Error", "No hay suficiente stock disponible", "error");
            return;
        }

        const item = carrito.find(i => i.id_producto == id);
        if (item) {
            const nuevaCantidad = item.cantidad + cantidad;
            if (nuevaCantidad <= stock) {
                item.cantidad = nuevaCantidad;
                Swal.fire({
                    icon: "success",
                    title: "¡Producto agregado!",
                    text: `Se añadieron ${cantidad} unidad(es) al carrito.`,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire("Error", "No hay suficiente stock", "error");
                return;
            }
        } else {
            carrito.push({ id_producto: id, nombre, precio, cantidad, stock, imagen });
            Swal.fire({
                icon: "success",
                title: "¡Producto agregado!",
                text: `Se añadieron ${cantidad} unidad(es) al carrito.`,
                timer: 1500,
                showConfirmButton: false
            });
        }
        actualizarCarrito();
        // Cargar productos relacionados después de agregar al carrito
        if (currentProductCategoryId) {
            cargarProductosRelacionados(currentProductCategoryId);
        }
    });

    // Redirigir al hacer clic en la imagen o el nombre de un producto relacionado
    relatedProductsList.addEventListener("click", function (e) {
        if (e.target.classList.contains("product-image-related") || e.target.classList.contains("product-name-related")) {
            const id = e.target.dataset.id;
            window.location.href = `http://localhost/fory-final/php/ModuloCliente/detalle_producto.php?id=${id}`;
        }
    });

    // Agregar productos relacionados al carrito
    relatedProductsList.addEventListener("click", function (e) {
        if (e.target.classList.contains("agregar-carrito")) {
            const id = e.target.dataset.id;
            const nombre = e.target.dataset.nombre;
            const precio = parseFloat(e.target.dataset.precio);
            const stock = parseInt(e.target.dataset.stock);
            const imagen = e.target.dataset.imagen;
            const cantidad = 1; // Cantidad fija para productos relacionados

            const item = carrito.find(i => i.id_producto == id);
            if (item) {
                const nuevaCantidad = item.cantidad + cantidad;
                if (nuevaCantidad <= stock) {
                    item.cantidad = nuevaCantidad;
                    Swal.fire({
                        icon: "success",
                        title: "¡Producto agregado!",
                        text: `Se añadió ${cantidad} unidad al carrito.`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire("Error", "No hay suficiente stock", "error");
                    return;
                }
            } else {
                carrito.push({ id_producto: id, nombre, precio, cantidad, stock, imagen });
                Swal.fire({
                    icon: "success",
                    title: "¡Producto agregado!",
                    text: `Se añadió ${cantidad} unidad al carrito.`,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
            actualizarCarrito();
        }
    });

    // Mostrar el carrito
    verCarrito.addEventListener("click", function (e) {
        e.preventDefault();
        cartSidebar.classList.add("open");
    });

    // Cerrar el carrito
    cerrarCarritoX.addEventListener("click", function () {
        cartSidebar.classList.remove("open");
    });

    cerrarCarritoFooter.addEventListener("click", function () {
        cartSidebar.classList.remove("open");
    });

    // Actualizar cantidad en el carrito
    carritoBody.addEventListener("change", function (e) {
        if (e.target.classList.contains("cantidad-carrito")) {
            const id = e.target.dataset.id;
            const cantidad = parseInt(e.target.value);
            const item = carrito.find(i => i.id_producto == id);
            if (cantidad > item.stock) {
                Swal.fire("Error", "No hay suficiente stock", "error");
                e.target.value = item.cantidad;
                return;
            }
            item.cantidad = cantidad;
            if (item.cantidad <= 0) {
                carrito = carrito.filter(i => i.id_producto != id);
            }
            actualizarCarrito();
        }
    });

    // Eliminar del carrito
    carritoBody.addEventListener("click", function (e) {
        if (e.target.classList.contains("eliminar-carrito") || e.target.parentElement.classList.contains("eliminar-carrito")) {
            const id = e.target.dataset.id || e.target.parentElement.dataset.id;
            carrito = carrito.filter(i => i.id_producto != id);
            actualizarCarrito();
        }
    });

    // Continuar pedido
    continuarBtn.addEventListener("click", function () {
        Swal.fire({
            icon: "info",
            title: "Costo de Envío",
            text: "El costo de envío es de $30 por pedido.",
            confirmButtonText: "Continuar"
        }).then((result) => {
            if (result.isConfirmed) {
                new bootstrap.Modal(document.getElementById("modal-direccion")).show();
            }
        });
    });

    // Agregar nueva dirección
    agregarDireccionBtn.addEventListener("click", function () {
        bootstrap.Modal.getInstance(document.getElementById("modal-direccion")).hide();
        new bootstrap.Modal(document.getElementById("modal-nueva-direccion")).show();
    });

    // Continuar a método de pago
    continuarPagoBtn.addEventListener("click", function () {
        if (!direccionSelect.value) {
            Swal.fire("Error", "Selecciona una dirección", "error");
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById("modal-direccion")).hide();
        new bootstrap.Modal(document.getElementById("modal-pago")).show();
    });

    // Validación y envío de nueva dirección
    formNuevaDireccion.addEventListener("submit", function (e) {
        e.preventDefault();
        if (!formNuevaDireccion.checkValidity()) {
            e.stopPropagation();
            formNuevaDireccion.classList.add("was-validated");
            return;
        }

        const formData = new FormData();
        formData.append("ope", "guardarDireccion");
        formData.append("calle", document.getElementById("calle").value);
        formData.append("numero", document.getElementById("numero").value);
        formData.append("colonia", document.getElementById("colonia").value);
        formData.append("ciudad", document.getElementById("ciudad").value);
        formData.append("estado", document.getElementById("estado").value);
        formData.append("codigo_postal", document.getElementById("codigo_postal").value);
        formData.append("referencias", document.getElementById("referencias").value);

        fetch("../../controladores/ControladorCliente/controlador_pedidos.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Dirección guardada!",
                    text: data.mensaje
                }).then(() => {
                    bootstrap.Modal.getInstance(document.getElementById("modal-nueva-direccion")).hide();
                    cargarDirecciones();
                    new bootstrap.Modal(document.getElementById("modal-direccion")).show();
                });
            } else {
                Swal.fire("Error", data.mensaje, "error");
            }
        });
    });

    // Mostrar u ocultar campos de pago según el método seleccionado
    metodoPagoSelect.addEventListener("change", function () {
        const metodo = metodoPagoSelect.value;
        transferenciaInfo.style.display = metodo === "2" ? "block" : "none"; // Transferencia
        telefonoContainer.style.display = metodo === "1" ? "block" : "none"; // Efectivo
        comprobanteContainer.style.display = metodo === "2" ? "block" : "none"; // Transferencia
        // Actualizar requerimientos de los campos
        document.getElementById("telefono-confirmacion").required = metodo === "1";
        document.getElementById("comprobante-pago").required = metodo === "2";
        // Limpiar campos al cambiar método
        if (metodo !== "1") document.getElementById("telefono-confirmacion").value = "";
        if (metodo !== "2") document.getElementById("comprobante-pago").value = "";
    });

    // Enviar pedido con validaciones
    formPago.addEventListener("submit", function (e) {
        e.preventDefault();
        formPago.classList.add("was-validated");

        if (!metodoPagoSelect.value) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor selecciona un método de pago"
            });
            return;
        }

        // Validar campos según método de pago
        if (metodoPagoSelect.value === "1") { // Efectivo
            const telefono = document.getElementById("telefono-confirmacion").value;
            if (!telefono) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Por favor ingresa un número telefónico"
                });
                return;
            }
            if (!/^\d{10}$/.test(telefono)) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "El número telefónico debe tener exactamente 10 dígitos"
                });
                return;
            }
        } else if (metodoPagoSelect.value === "2") { // Transferencia
            const comprobante = document.getElementById("comprobante-pago").files[0];
            if (!comprobante) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Por favor sube un comprobante de pago"
                });
                return;
            }
            const validTypes = ["image/jpeg", "image/png", "application/pdf"];
            if (!validTypes.includes(comprobante.type)) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "El comprobante debe ser JPEG, PNG o PDF"
                });
                return;
            }
            if (comprobante.size > 5 * 1024 * 1024) { // 5MB
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "El archivo no debe exceder los 5MB"
                });
                return;
            }
        }

        const formData = new FormData();
        formData.append("ope", "guardarPedido");
        formData.append("id_establecimiento", 1); // Establecimiento fijo
        formData.append("id_direccion", direccionSelect.value);
        formData.append("id_metodo_pago", metodoPagoSelect.value);
        formData.append("carrito", JSON.stringify(carrito));
        if (metodoPagoSelect.value === "1") {
            formData.append("telefono_confirmacion", document.getElementById("telefono-confirmacion").value);
        } else if (metodoPagoSelect.value === "2") {
            formData.append("comprobante_pago", document.getElementById("comprobante-pago").files[0]);
        }

        fetch("../../controladores/ControladorCliente/controlador_pedidos.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Pedido Realizado",
                    text: data.mensaje
                }).then(() => {
                    carrito = [];
                    actualizarCarrito();
                    bootstrap.Modal.getInstance(document.getElementById("modal-pago")).hide();
                    window.location.href = `http://localhost/fory-final/php/ModuloCliente/cliente.php?pedido=${data.id_pedido}`;
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.mensaje
                });
            }
        });
    });

    // Inicializar el carrito y cargar datos
    actualizarCarrito();
    cargarDirecciones();
    cargarMetodosPago();
});