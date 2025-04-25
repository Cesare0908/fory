document.addEventListener("DOMContentLoaded", function () {
    const historialCompras = document.getElementById("historial-compras");
    const sidebar = document.getElementById("sidebar");
    const abrirSidebar = document.getElementById("abrir-sidebar");
    const cerrarSidebar = document.getElementById("cerrar-sidebar");

    // Cargar historial de compras
    function cargarHistorialCompras() {
        fetch("../../controladores/ControladorCliente/controlador_pedidos.php?ope=listarHistorialCompras")
            .then(response => response.json())
            .then(data => {
                historialCompras.innerHTML = "";
                if (data.success && data.pedidos.length > 0) {
                    data.pedidos.forEach(pedido => {
                        const card = document.createElement("div");
                        card.className = "card mb-3";
                        card.innerHTML = `
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pedido #${pedido.id_pedido} - ${new Date(pedido.fecha_pedido).toLocaleDateString()}</h5>
                                <button class="btn btn-outline-primary btn-sm toggle-detalle" data-id="${pedido.id_pedido}">
                                    <i class="fas fa-chevron-down"></i> Detalles
                                </button>
                            </div>
                            <div class="card-body detalle-pedido" id="detalle-${pedido.id_pedido}" style="display: none;">
                                <p><strong>Estado:</strong> ${pedido.estado}</p>
                                <p><strong>Dirección:</strong> ${pedido.direccion}</p>
                                <p><strong>Método de Pago:</strong> ${pedido.nombre_metodo}</p>
                                <p><strong>Costo de Envío:</strong> $${pedido.costo_envio}</p>
                                <p><strong>Total:</strong> $${pedido.total}</p>
                                <h6>Productos:</h6>
                                <ul class="list-group mb-3">
                                    ${pedido.detalles.map(detalle => `
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <img src="${detalle.imagen}" alt="${detalle.nombre_producto}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                                    ${detalle.nombre_producto}
                                                </div>
                                                <div>
                                                    ${detalle.cantidad} x $${detalle.precio_unitario} = $${detalle.subtotal}
                                                </div>
                                            </div>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        `;
                        historialCompras.appendChild(card);
                    });
                } else {
                    historialCompras.innerHTML = `<p class="text-center text-muted">No tienes pedidos en tu historial.</p>`;
                }
            });
    }

    // Mostrar/ocultar detalles del pedido
    historialCompras.addEventListener("click", function (e) {
        if (e.target.classList.contains("toggle-detalle") || e.target.parentElement.classList.contains("toggle-detalle")) {
            const btn = e.target.classList.contains("toggle-detalle") ? e.target : e.target.parentElement;
            const id = btn.dataset.id;
            const detalle = document.getElementById(`detalle-${id}`);
            const icon = btn.querySelector("i");
            if (detalle.style.display === "none") {
                detalle.style.display = "block";
                icon.classList.remove("fa-chevron-down");
                icon.classList.add("fa-chevron-up");
                btn.innerHTML = `<i class="fas fa-chevron-up"></i> Ocultar`;
            } else {
                detalle.style.display = "none";
                icon.classList.remove("fa-chevron-up");
                icon.classList.add("fa-chevron-down");
                btn.innerHTML = `<i class="fas fa-chevron-down"></i> Detalles`;
            }
        }
    });

    // Mostrar/ocultar sidebar
    abrirSidebar.addEventListener("click", function () {
        sidebar.classList.add("open");
    });

    cerrarSidebar.addEventListener("click", function () {
        sidebar.classList.remove("open");
    });

    // Cerrar sidebar al hacer clic fuera
    document.addEventListener("click", function (e) {
        if (sidebar.classList.contains("open") && 
            !sidebar.contains(e.target) && 
            !abrirSidebar.contains(e.target)) {
            sidebar.classList.remove("open");
        }
    });

    // Cargar datos iniciales
    cargarHistorialCompras();
});