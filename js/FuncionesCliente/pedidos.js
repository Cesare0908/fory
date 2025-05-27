document.addEventListener("DOMContentLoaded", function () {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    const establecimientoInput = document.getElementById("establecimiento");
    const categoryCarousel = document.getElementById("category-carousel");
    const listaProductos = document.getElementById("lista-productos");
    const listaProductosContainer = document.getElementById("lista-productos-container");
    const listaProductosTitle = document.getElementById("lista-productos-title");
    const productosMasVendidos = document.getElementById("productos-mas-vendidos");
    const productosMasVendidosContainer = document.getElementById("productos-mas-vendidos-container");
    const productosRecomendados = document.getElementById("productos-recomendados");
    const productosRecomendadosContainer = document.getElementById("productos-recomendados-container");
    const productosRelacionados = document.getElementById("productos-relacionados");
    const productosRelacionadosContainer = document.getElementById("productos-relacionados-container");
    const carritoBody = document.getElementById("carrito-body");
    const subtotalCarrito = document.getElementById("subtotal-carrito");
    const totalCarrito = document.getElementById("total-carrito");
    const continuarBtn = document.getElementById("continuar-pedido");
    const cerrarCarritoFooter = document.getElementById("cerrar-carrito-footer");
    const cartSidebar = document.getElementById("cart-sidebar");
    const verCarrito = document.getElementById("ver-carrito");
    const ocultarCarrito = document.getElementById("ocultar-carrito");
    const cerrarCarrito = document.getElementById("cerrar-carrito");
    const cerrarCarritoX = document.getElementById("cerrar-carrito-x");
    const floatingCart = document.getElementById("floating-cart");
    const direccionSelect = document.getElementById("direccion");
    const agregarDireccionBtn = document.getElementById("agregar-direccion");
    const continuarPagoBtn = document.getElementById("continuar-pago");
    const metodoPagoSelect = document.getElementById("metodo-pago");
    const transferenciaInfo = document.getElementById("transferencia-info");
    const telefonoContainer = document.getElementById("telefono-container");
    const comprobanteContainer = document.getElementById("comprobante-container");
    const formPago = document.getElementById("form-pago");
    const buscador = document.getElementById("buscador");
    const cartCounter = document.getElementById("cart-counter");
    const floatingCartCounter = document.getElementById("floating-cart-counter");
    const sidebar = document.getElementById("sidebar");
    const abrirSidebar = document.getElementById("abrir-sidebar");
    const cerrarSidebar = document.getElementById("cerrar-sidebar");
    const formNuevaDireccion = document.getElementById("form-nueva-direccion");

    // Íconos para categorías
    const categoryIcons = {
        1: "fas fa-pizza-slice",
        2: "fas fa-hamburger",
        3: "fas fa-coffee",
        4: "fas fa-ice-cream",
        5: "fas fa-utensils"
    };

    // Cargar categorías
    fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarCategorias")
        .then(response => response.json())
        .then(data => { 
            if (data.success) {
                categoryCarousel.innerHTML = `
                    <button class="btn btn-outline-dark me-2 category-btn active" data-id="0"><i class="fas fa-th-large me-1"></i> Todas</button>
                `;
                data.categorias.forEach(cat => {
                    const button = document.createElement("button");
                    button.className = "btn btn-outline-dark me-2 category-btn";
                    button.dataset.id = cat.id_categoria;
                    button.innerHTML = `<i class="${categoryIcons[cat.id_categoria] || 'fas fa-utensils'} me-1"></i> ${cat.nombre_categoria}`;
                    categoryCarousel.appendChild(button);
                });
            }
        });

    // Cargar productos recomendados
    function cargarProductosRecomendados() {
        fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarProductosRecomendados")
            .then(response => response.json())
            .then(data => {
                productosRecomendados.innerHTML = "";
                if (data.success && data.productos.length > 0) {
                    productosRecomendadosContainer.style.display = "block";
                    data.productos.forEach(prod => {
                        const card = document.createElement("div");
                        card.className = "product-item";
                        card.innerHTML = `
                            <div class="card product-card">
                                <img src="${prod.imagen}" class="card-img-top product-image" alt="${prod.nombre_producto}" data-id="${prod.id_producto}">
                                <div class="card-body">
                                    <h5 class="card-title product-name" data-id="${prod.id_producto}">${prod.nombre_producto}</h5>
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
                        productosRecomendados.appendChild(card);
                    });
                } else {
                    productosRecomendadosContainer.style.display = "none";
                }
            });
    }

    // Cargar productos relacionados
    function cargarProductosRelacionados(idProducto, idCategoria) {
        fetch(`../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarProductosRelacionados&id_categoria=${idCategoria}&id_producto=${idProducto}`)
            .then(response => response.json())
            .then(data => {
                productosRelacionados.innerHTML = "";
                if (data.success && data.productos.length > 0) {
                    productosRelacionadosContainer.style.display = "block";
                    data.productos.forEach(prod => {
                        const card = document.createElement("div");
                        card.className = "product-item";
                        card.innerHTML = `
                            <div class="card product-card">
                                <img src="${prod.imagen}" class="card-img-top product-image" alt="${prod.nombre_producto}" data-id="${prod.id_producto}">
                                <div class="card-body">
                                    <h5 class="card-title product-name" data-id="${prod.id_producto}">${prod.nombre_producto}</h5>
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
                        productosRelacionados.appendChild(card);
                    });
                } else {
                    productosRelacionadosContainer.style.display = "none";
                }
            });
    }

    // Cargar productos más vendidos
    function cargarProductosMasVendidos() {
        fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarProductosMasVendidos")
            .then(response => response.json())
            .then(data => {
                productosMasVendidos.innerHTML = "";
                if (data.success) {
                    data.productos.forEach(prod => {
                        const card = document.createElement("div");
                        card.className = "product-item";
                        card.innerHTML = `
                            <div class="card product-card">
                                <img src="${prod.imagen}" class="card-img-top product-image" alt="${prod.nombre_producto}" data-id="${prod.id_producto}">
                                <div class="card-body">
                                    <h5 class="card-title product-name" data-id="${prod.id_producto}">${prod.nombre_producto}</h5>
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
                        productosMasVendidos.appendChild(card);
                    });
                }
            });
    }

    // Cargar direcciones
    function cargarDirecciones() {
        direccionSelect.innerHTML = '<option value="">Selecciona una dirección</option>';
        fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarDirecciones")
            .then(response => response.json())
            .then(data => {
                if (data.success && data.direcciones.length > 0) {
                    data.direcciones.forEach(dir => {
                        const option = document.createElement("option");
                        option.value = dir.id_direccion;
                        option.textContent = `${dir.calle} ${dir.numero}, ${dir.colonia}, ${dir.ciudad}`;
                        direccionSelect.appendChild(option);
                    });
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Sin direcciones",
                        text: "No tienes direcciones registradas. Agrega una nueva dirección.",
                    }).then(() => {
                        bootstrap.Modal.getInstance(document.getElementById("modal-direccion")).hide();
                        new bootstrap.Modal(document.getElementById("modal-nueva-direccion")).show();
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar las direcciones. Intenta de nuevo.",
                });
                console.error("Error fetching direcciones:", error);
            });
    }

    // Cargar métodos de pago
    fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarMetodosPago")
        .then(response => response.json())
        .then(data => {
            if (data.success && data.metodos.length > 0) {
                data.metodos.forEach(met => {
                    const option = document.createElement("option");
                    option.value = met.id_metodo_pago;
                    option.textContent = met.nombre_metodo;
                    metodoPagoSelect.appendChild(option);
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los métodos de pago.",
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error al cargar métodos de pago. Intenta de nuevo.",
            });
            console.error("Error fetching metodos pago:", error);
        });

    // Cargar productos
    function cargarProductos(idCategoria = 0, busqueda = "") {
        let url = `../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarProductos`;
        if (idCategoria > 0) url += `&id_categoria=${idCategoria}`;
        if (busqueda) url += `&busqueda=${encodeURIComponent(busqueda)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                listaProductos.innerHTML = "";
                if (idCategoria > 0) {
                    listaProductosTitle.textContent = `Productos de la categoría`;
                    listaProductosContainer.style.display = "block";
                } else if (busqueda) {
                    listaProductosTitle.textContent = `Resultados de la búsqueda`;
                    listaProductosContainer.style.display = "block";
                } else {
                    listaProductosTitle.textContent = `Explora el Menú`;
                    listaProductosContainer.style.display = "block";
                }

                if (data.success && data.productos.length > 0) {
                    data.productos.forEach(prod => {
                        const card = document.createElement("div");
                        card.className = "product-item";
                        card.innerHTML = `
                            <div class="card product-card">
                                <img src="${prod.imagen}" class="card-img-top product-image" alt="${prod.nombre_producto}" data-id="${prod.id_producto}">
                                <div class="card-body">
                                    <h5 class="card-title product-name" data-id="${prod.id_producto}">${prod.nombre_producto}</h5>
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
                        listaProductos.appendChild(card);
                    });
                } else {
                    listaProductos.innerHTML = `<p class="text-center text-muted">Aún no hay productos en esta categoría.</p>`;
                }
                // Cargar recomendaciones después de productos
                cargarProductosRecomendados();
            });
    }

    // Actualizar carrito
    function actualizarCarrito() {
        carritoBody.innerHTML = "";
        let subtotal = 0;
        let itemCount = 0;
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
        subtotalCarrito.textContent = subtotal.toFixed(2);
        totalCarrito.textContent = (subtotal + 30).toFixed(2);
        cartCounter.textContent = itemCount;
        floatingCartCounter.textContent = itemCount;
        continuarBtn.disabled = carrito.length === 0;
        localStorage.setItem("carrito", JSON.stringify(carrito));
    }

    // Agregar al carrito
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("agregar-carrito")) {
            const id = e.target.dataset.id;
            const nombre = e.target.dataset.nombre;
            const precio = parseFloat(e.target.dataset.precio);
            const stock = parseInt(e.target.dataset.stock);
            const imagen = e.target.dataset.imagen;

            if (stock <= 0) {
                Swal.fire("Error", "Este producto no está disponible", "error");
                return;
            }

            const item = carrito.find(i => i.id_producto == id);
            if (item) {
                if (item.cantidad < stock) {
                    item.cantidad++;
                    Swal.fire({
                        icon: "success",
                        title: "¡Producto agregado!",
                        text: "Se añadió 1 unidad al carrito.",
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire("Error", "No hay suficiente stock", "error");
                    return;
                }
            } else {
                carrito.push({ id_producto: id, nombre, precio, cantidad: 1, stock, imagen });
                Swal.fire({
                    icon: "success",
                    title: "¡Producto agregado!",
                    text: "Se añadió al carrito.",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
            actualizarCarrito();
            // Cargar productos relacionados después de agregar al carrito
            fetch(`../../controladores/ControladorCliente/controlador_pedidos.php?ope=obtenerProducto&id_producto=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarProductosRelacionados(id, data.producto.id_categoria);
                    }
                });
        }
    });

    // Redirigir a la página de detalles del producto
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("product-image") || e.target.classList.contains("product-name")) {
            const id = e.target.dataset.id;
            window.location.href = `http://localhost/fory-final/php/ModuloCliente/detalle_producto.php?id=${id}`;
        }
    });

    // Filtrar por categoría
    categoryCarousel.addEventListener("click", function (e) {
        if (e.target.classList.contains("category-btn") || e.target.parentElement.classList.contains("category-btn")) {
            const btn = e.target.classList.contains("category-btn") ? e.target : e.target.parentElement;
            const idCategoria = btn.dataset.id;
            document.querySelectorAll(".category-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            cargarProductos(idCategoria);
        }
    });

    // Buscador
    buscador.addEventListener("input", function () {
        const busqueda = buscador.value.trim();
        cargarProductos(0, busqueda);
    });

    // Actualizar cantidad en carrito
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

    // Mostrar/ocultar carrito
    verCarrito.addEventListener("click", function (e) {
        e.preventDefault();
        cartSidebar.classList.add("open");
    });

    floatingCart.addEventListener("click", function () {
        cartSidebar.classList.add("open");
    });

    ocultarCarrito.addEventListener("click", function () {
        cartSidebar.classList.remove("open");
    });

    cerrarCarrito.addEventListener("click", function () {
        cartSidebar.classList.remove("open");
    });

    cerrarCarritoX.addEventListener("click", function () {
        cartSidebar.classList.remove("open");
    });

    cerrarCarritoFooter.addEventListener("click", function () {
        cartSidebar.classList.remove("open");
    });

    // Mostrar/ocultar sidebar
    abrirSidebar.addEventListener("click", function () {
        sidebar.classList.add("open");
    });

    cerrarSidebar.addEventListener("click", function () {
        sidebar.classList.remove("open");
    });

    // Cerrar menús laterales al hacer clic fuera
    document.addEventListener("click", function (e) {
        if (sidebar.classList.contains("open") && 
            !sidebar.contains(e.target) && 
            !abrirSidebar.contains(e.target)) {
            sidebar.classList.remove("open");
        }
        if (cartSidebar.classList.contains("open") && 
            !cartSidebar.contains(e.target) && 
            !verCarrito.contains(e.target) && 
            !floatingCart.contains(e.target)) {
            cartSidebar.classList.remove("open");
        }
    });

    // Continuar pedido
    continuarBtn.addEventListener("click", function () {
        if (!establecimientoInput.value) {
            Swal.fire("Error", "Selecciona una tienda", "error");
            return;
        }
        Swal.fire({
            icon: "info",
            title: "Costo de Envío",
            text: "El costo de envío es de $30 por pedido.",
            confirmButtonText: "Continuar"
        }).then((result) => {
            if (result.isConfirmed) {
                cargarDirecciones(); // Refrescar direcciones
                setTimeout(() => {
                    new bootstrap.Modal(document.getElementById("modal-direccion")).show();
                }, 500);
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
        if (!direccionSelect.value || direccionSelect.value === "") {
            Swal.fire("Error", "Selecciona una dirección válida", "error");
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

        // Validar campos obligatorios
        if (!metodoPagoSelect.value || metodoPagoSelect.value === "") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor selecciona un método de pago válido"
            });
            return;
        }
        if (!direccionSelect.value || direccionSelect.value === "") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor selecciona una dirección válida"
            });
            return;
        }
        if (!establecimientoInput.value || parseInt(establecimientoInput.value) <= 0) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "El establecimiento no es válido"
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
        formData.append("id_establecimiento", establecimientoInput.value);
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
                        window.location.href = `../../php/ModuloCliente/cliente.php?pedido=${data.id_pedido}`;
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

    // Carrusel de categorías y productos
    document.querySelectorAll(".carousel-control-prev, .carousel-control-next").forEach(button => {
        button.addEventListener("click", function () {
            const targetId = this.dataset.target;
            const carousel = document.getElementById(targetId);
            const scrollAmount = carousel.clientWidth * 0.8;
            if (this.classList.contains("carousel-control-prev")) {
                carousel.scrollBy({ left: -scrollAmount, behavior: "smooth" });
            } else {
                carousel.scrollBy({ left: scrollAmount, behavior: "smooth" });
            }
        });
    });

    // Cargar datos iniciales
    cargarProductos();
    cargarProductosMasVendidos();
    cargarDirecciones();
    actualizarCarrito();
});