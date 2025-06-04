document.addEventListener("DOMContentLoaded", () => {
    let filtroEstado = 'pendiente'; // Por defecto mostrar pendientes
    cargarSelects();

    // Configuración de la tabla de pedidos
    if (document.querySelector("#tablaPedidos")) {
        const modalDetallesPedido = new bootstrap.Modal(document.getElementById('modalDetallesPedido'));
        const modalEditarPedido = new bootstrap.Modal(document.getElementById('modalEditarPedido'));
        const modalNuevoPedido = new bootstrap.Modal(document.getElementById('modalNuevoPedido'));

        const grid = new gridjs.Grid({
            columns: [
                { name: 'ID', width: '10%' },
                { name: 'Cliente', width: '20%' },
                { name: 'Establecimiento', width: '20%' },
                { name: 'Fecha', width: '15%' },
                {
                    name: 'Estado',
                    width: '15%',
                    formatter: (cell) => {
                        const clases = {
                            'pendiente': 'bg-warning',
                            'en preparacioón': 'bg-primary',
                            'en camino': 'bg-primary',
                            'entregado': 'bg-success',
                            'cancelado': 'bg-danger'
                        };
                        return gridjs.html(`<span class="badge ${clases[cell] || 'bg-secondary'}">${cell.charAt(0).toUpperCase() + cell.slice(1)}</span>`);
                    }
                },
                { name: 'Total', width: '10%', formatter: (cell) => `$${parseFloat(cell).toFixed(2)}` },
                {
                    name: 'Acciones',
                    formatter: (_, rows) => gridjs.html(`
                        <button class="btn btn-sm action-btn details-btn ver-detalles" 
                            data-id="${rows.cells[0].data}"
                            title="Ver detalles del pedido">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn edit-btn editar" 
                            data-id="${rows.cells[0].data}" 
                            title="Editar pedido">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm action-btn delete-btn cancelar" 
                            data-id="${rows.cells[0].data}"
                            data-estado="${rows.cells[4].data}"
                            title="Cancelar pedido">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    `),
                    sort: false,
                    width: '10%'
                }
            ],
            pagination: true,
            search: true,
            sort: true,
            resizable: true,
            server: {
                url: `http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaPedidos&estado=${filtroEstado}`,
                then: (data) => {
                    console.log("Datos recibidos:", data); // Depuración
                    return data.results.map((pedido) => [
                        pedido.id_pedido,
                        pedido.cliente,
                        pedido.establecimiento,
                        pedido.fecha_pedido,
                        pedido.estado,
                        pedido.total
                    ]);
                }
            },
            language: {
                search: { placeholder: '🔎 Buscar por cliente o ID...' },
                pagination: {
                    previous: '⬅️',
                    next: '➡️',
                    showing: 'Mostrando',
                    of: 'de',
                    to: 'a',
                    results: 'pedidos',
                },
                loading: 'Cargando...',
                noRecordsFound: 'No se encontraron pedidos.',
                error: 'Error al cargar los datos.'
            }
        }).render(document.querySelector("#tablaPedidos"));

        // Filtro por estado
        document.querySelectorAll('.dropdown-item[data-estado]').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                filtroEstado = e.target.getAttribute('data-estado');
                grid.updateConfig({
                    server: {
                        url: `http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaPedidos&estado=${filtroEstado}`,
                        then: (data) => {
                            console.log("Datos filtrados:", data); // Depuración
                            return data.results.map((pedido) => [
                                pedido.id_pedido,
                                pedido.cliente,
                                pedido.establecimiento,
                                pedido.fecha_pedido,
                                pedido.estado,
                                pedido.total
                            ]);
                        }
                    }
                }).forceRender();
                document.querySelector('#filtroEstado').textContent = filtroEstado ? filtroEstado.charAt(0).toUpperCase() + filtroEstado.slice(1) : 'Filtrar por Estado';
            });
        });

        // Actualizar lista
        document.querySelector("#actListadoPedidos").addEventListener("click", (event) => {
            event.preventDefault();
            grid.updateConfig({
                server: {
                    url: `http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaPedidos&estado=${filtroEstado}`,
                    then: (data) => {
                        console.log("Datos actualizados:", data); // Depuración
                        return data.results.map((pedido) => [
                            pedido.id_pedido,
                            pedido.cliente,
                            pedido.establecimiento,
                            pedido.fecha_pedido,
                            pedido.estado,
                            pedido.total
                        ]);
                    }
                }
            }).forceRender();
        });

        // Exportar PDF
        document.querySelector("#exportarPDF").addEventListener("click", () => {
            window.location.href = `http://localhost/fory-final/consultas/exportarPedidosPdf.php?formato=pdf&estado=${filtroEstado}`;
        });

        // Exportar Excel
        document.querySelector("#exportarExcel").addEventListener("click", () => {
            window.location.href = `http://localhost/fory-final/consultas/exportarPedidosPdf.php?formato=excel&estado=${filtroEstado}`;
        });

        // Acciones en la tabla
        document.querySelector("#tablaPedidos").addEventListener("click", (event) => {
            event.preventDefault();
            let ele = event.target.closest('button');

            // Ver detalles
            if (ele?.classList.contains("ver-detalles")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarPedido");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/fory-final/controladores/controladorPedidos.php');
                xhr.responseType = "json";
                xhr.send(info);
 
                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        const r = xhr.response;
                        document.querySelector("#detalleID").textContent = r.id_pedido;
                        document.querySelector("#detalleCliente").textContent = r.cliente;
                        document.querySelector("#detalleRepartidor").textContent = r.repartidor;
                        document.querySelector("#detalleEstablecimiento").textContent = r.establecimiento;
                        document.querySelector("#detalleDireccion").textContent = r.direccion;
                        document.querySelector("#detalleFecha").textContent = r.fecha_pedido;
                        document.querySelector("#detalleEstado").innerHTML = `<span class="badge ${getBadgeClass(r.estado)}">${r.estado.charAt(0).toUpperCase() + r.estado.slice(1)}</span>`;
                        document.querySelector("#detalleTotal").textContent = `$${parseFloat(r.total).toFixed(2)}`;
                        document.querySelector("#detalleTiempoEstimado").textContent = r.tiempo_estimado || 'No especificado';
                        document.querySelector("#detalleTiempoReal").textContent = r.tiempo_real || 'No registrado';
                        document.querySelector("#detalleMetodoPago").textContent = r.metodo_pago;
                        document.querySelector("#detalleCostoEnvio").textContent = `$${parseFloat(r.costo_envio).toFixed(2)}`;
                        document.querySelector("#detalleTelefono").textContent = r.telefono_confirmacion || 'No aplica';
                        document.querySelector("#detalleComprobante").innerHTML = r.comprobante_pago ? 
                            `<a href="../comprobantes/${r.comprobante_pago}" target="_blank">Ver Comprobante</a>` : 
                            'No aplica';
                        document.querySelector("#detalleNotas").textContent = r.notas || 'Sin notas';

                        // Tabla de productos
                        let tbody = document.querySelector("#detalleProductos");
                        tbody.innerHTML = '';
                        r.detalles.forEach(detalle => {
                            let row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${detalle.nombre_producto}</td>
                                <td>${detalle.cantidad}</td>
                                <td>$${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                                <td>$${parseFloat(detalle.subtotal).toFixed(2)}</td>
                            `;
                            tbody.appendChild(row);
                        });

                        // Advertencia si no hay repartidor
                        if (!r.id_repartidor) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Repartidor No Asignado',
                                text: 'Por favor asigna un repartidor a este pedido.',
                            });
                        }

                        modalDetallesPedido.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el pedido',
                        });
                    }
                };
            }

            // Editar pedido
            if (ele?.classList.contains("editar")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarPedido");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/fory-final/controladores/controladorPedidos.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#editarIDPedido").value = xhr.response.id_pedido;
                        document.querySelector("#editarEstado").value = xhr.response.estado;
                        document.querySelector("#editarRepartidor").value = xhr.response.id_repartidor || '';
                        modalEditarPedido.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el pedido',
                        });
                    }
                };
            }

            // Cancelar pedido
            if (ele?.classList.contains("cancelar")) {
                let id = ele.getAttribute("data-id");
                let estado = ele.getAttribute("data-estado");

                if (estado === 'entregado') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acción Prohibida',
                        text: 'No se pueden cancelar pedidos ya entregados.',
                    });
                    return;
                }

                Swal.fire({
                    title: "¿Está Usted Seguro?",
                    text: "Intenta cancelar el pedido #" + id,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, cancelarlo!",
                    cancelButtonText: "No"
                }).then((result) => {
                    if (result.isConfirmed) {
                        let info = new FormData();
                        info.append("ope", "cancelarPedido");
                        info.append("id", id);

                        let xhr = new XMLHttpRequest();
                        xhr.open('POST', 'http://localhost/fory-final/controladores/controladorPedidos.php');
                        xhr.send(info);

                        xhr.onload = function () {
                            let response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Cancelado',
                                    text: response.message,
                                });
                                document.querySelector("#actListadoPedidos").click();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo cancelar el pedido',
                                });
                            }
                        };
                    }
                });
            }
        });

        // Agregar nuevo pedido
        document.querySelector("#btnAgregarPedido").addEventListener("click", () => {
            modalNuevoPedido.show();
            document.querySelector("#formNuevoPedido").reset();
            document.querySelector("#listaProductos").innerHTML = '';
            document.querySelector("#total_calculado").value = '0.00';
            agregarFilaProducto();
            cargarSelects();
            document.querySelectorAll('#formNuevoPedido .form-control').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                if (input.nextElementSibling?.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.textContent = '';
                }
            });
        });

        // Guardar nuevo pedido
        document.querySelector("#btnGuardarPedido").addEventListener("click", (event) => {
            event.preventDefault();
            if (!validarFormularioNuevo()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formData = new FormData(document.querySelector("#formNuevoPedido"));
            formData.append("ope", "guardarPedido");

            // Agregar productos
            let productos = [];
            document.querySelectorAll('.producto-fila').forEach(fila => {
                const idProducto = fila.querySelector('.select-producto').value;
                const cantidad = fila.querySelector('.cantidad-producto').value;
                if (idProducto && cantidad > 0) {
                    productos.push({
                        id_producto: idProducto,
                        cantidad: parseInt(cantidad)
                    });
                }
            });
            formData.append("productos", JSON.stringify(productos));
            console.log("Enviando datos:", Object.fromEntries(formData)); // Depuración

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "http://localhost/fory-final/controladores/controladorPedidos.php", true);
            xhr.onload = function () {
                let response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    console.error("Respuesta no válida:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Respuesta del servidor no válida',
                    });
                    return;
                }
                console.log("Respuesta del servidor:", response); // Depuración
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: response.mensaje,
                    }).then(() => {
                        document.querySelector("#formNuevoPedido").reset();
                        modalNuevoPedido.hide();
                        document.querySelector("#actListadoPedidos").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo guardar el pedido',
                    });
                }
            };
            xhr.onerror = function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión con el servidor',
                });
            };
            xhr.send(formData);
        });

        // Editar pedido
        document.querySelector("#btnEditarPedido").addEventListener("click", (event) => {
            event.preventDefault();
            if (!validarFormularioEdicion()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formData = new FormData(document.querySelector("#formEditarPedido"));
            formData.append("ope", "editarPedido");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "http://localhost/fory-final/controladores/controladorPedidos.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: response.mensaje,
                    }).then(() => {
                        modalEditarPedido.hide();
                        document.querySelector("#actListadoPedidos").click();
                        if (response.notificacion) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Notificación Enviada',
                                text: `Se notificó al repartidor: ${response.notificacion}`,
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo actualizar el pedido',
                    });
                }
            };
            xhr.send(formData);
        });

        // Agregar producto
        document.querySelector("#agregarProducto").addEventListener("click", () => {
            agregarFilaProducto();
            actualizarTotal();
        });

        // Actualizar total en tiempo real
        document.querySelector("#listaProductos").addEventListener("input", actualizarTotal);
        document.querySelector("#costo_envio").addEventListener("input", actualizarTotal);

        // Filtrar direcciones al cambiar cliente
        document.querySelector("#id_usuario").addEventListener("change", (e) => {
            const idUsuario = e.target.value;
            if (idUsuario) {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', `http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaDirecciones&id_usuario=${idUsuario}`);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        let direcciones = JSON.parse(xhr.responseText);
                        let select = document.getElementById('id_direccion');
                        select.innerHTML = '<option value="">Seleccione una dirección</option>';
                        direcciones.forEach(dir => {
                            let option = document.createElement('option');
                            option.value = dir.id_direccion;
                            option.textContent = `${dir.calle} ${dir.numero}, ${dir.colonia}, ${dir.ciudad}`;
                            select.appendChild(option);
                        });
                    }
                };
                xhr.send();
            } else {
                document.getElementById('id_direccion').innerHTML = '<option value="">Seleccione una dirección</option>';
            }
        });

        // Validaciones en tiempo real
        document.getElementById('id_usuario')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un cliente");
        });
        document.getElementById('id_establecimiento')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un establecimiento");
        });
        document.getElementById('id_direccion')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione una dirección");
        });
        document.getElementById('id_metodo_pago')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un método de pago");
        });
        document.getElementById('costo_envio')?.addEventListener('input', function () {
            validaMinimo(this, 0, "Ingrese un costo de envío válido");
        });
        document.getElementById('tiempo_estimado')?.addEventListener('input', function () {
            validaSeleccion(this, "Ingrese un tiempo estimado válido");
        });
        document.getElementById('editarEstado')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un estado");
        });
        document.getElementById('editarRepartidor')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un repartidor");
        });
    }
});

function cargarSelects() {
    // Clientes
    let xhrClientes = new XMLHttpRequest();
    xhrClientes.open('GET', 'http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaClientes');
    xhrClientes.onload = function () {
        if (xhrClientes.status === 200) {
            let clientes = JSON.parse(xhrClientes.responseText);
            let select = document.getElementById('id_usuario');
            select.innerHTML = '<option value="">Seleccione un cliente</option>';
            clientes.forEach(cliente => {
                let option = document.createElement('option');
                option.value = cliente.id_usuario;
                option.textContent = `${cliente.nombre} ${cliente.ap_paterno}`;
                select.appendChild(option);
            });
        }
    };
    xhrClientes.send();

    // Establecimientos
    let xhrEst = new XMLHttpRequest();
    xhrEst.open('GET', 'http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaEstablecimientos');
    xhrEst.onload = function () {
        if (xhrEst.status === 200) {
            let establecimientos = JSON.parse(xhrEst.responseText);
            let select = document.getElementById('id_establecimiento');
            select.innerHTML = '<option value="">Seleccione un establecimiento</option>';
            establecimientos.forEach(est => {
                let option = document.createElement('option');
                option.value = est.id_establecimiento;
                option.textContent = est.nombre;
                select.appendChild(option);
            });
        }
    };
    xhrEst.send();

    // Métodos de pago
    let xhrMet = new XMLHttpRequest();
    xhrMet.open('GET', 'http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaMetodosPago');
    xhrMet.onload = function () {
        if (xhrMet.status === 200) {
            let metodos = JSON.parse(xhrMet.responseText);
            let select = document.getElementById('id_metodo_pago');
            select.innerHTML = '<option value="">Seleccione un método</option>';
            metodos.forEach(met => {
                let option = document.createElement('option');
                option.value = met.id_metodo_pago;
                option.textContent = met.nombre_metodo;
                select.appendChild(option);
            });
        }
    };
    xhrMet.send();

    // Repartidores (para edición)
    let xhrRep = new XMLHttpRequest();
    xhrRep.open('GET', 'http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaRepartidores'); // Depuración
    xhrRep.onload = function () {
        if (xhrRep.status === 200) {
            let repartidores = JSON.parse(xhrRep.responseText);
            let select = document.getElementById('editarRepartidor');
            select.innerHTML = '<option value="">Seleccione un repartidor</option>';
            repartidores.forEach(rep => {
                let option = document.createElement('option');
                option.value = rep.id_repartidor;
                option.textContent = `${rep.nombre} (${rep.disponibilidad})`;
                select.appendChild(option);
            });
        }
    };
    xhrRep.send();

    // Productos
    let xhrProd = new XMLHttpRequest();
    xhrProd.open('GET', 'http://localhost/fory-final/controladores/controladorPedidos.php?ope1=ListaProductos'); // Depuración
    xhrProd.onload = function () {
        if (xhrProd.status === 200) {
            window.productosDisponibles = JSON.parse(xhrProd.responseText);
            // Actualizar filas existentes
            document.querySelectorAll('.select-producto').forEach(select => {
                const valorActual = select.value;
                select.innerHTML = '<option value="">Seleccione un producto</option>';
                window.productosDisponibles.forEach(p => {
                    let option = document.createElement('option');
                    option.value = p.id_producto;
                    option.textContent = `${p.nombre_producto} (Stock: ${p.stock})`;
                    option.dataset.precio = p.precio;
                    select.appendChild(option);
                });
                select.value = valorActual;
            });
        }
    };
    xhrProd.send();
}

function agregarFilaProducto() {
    let container = document.querySelector("#listaProductos");
    let fila = document.createElement('div');
    fila.classList.add('producto-fila', 'row', 'g-3', 'mb-2');
    fila.innerHTML = `
        <div class="col-md-6">
            <select class="form-control select-producto" name="producto[]" required>
                <option value="">Seleccione un producto</option>
                ${window.productosDisponibles?.map(p => `<option value="${p.id_producto}" data-precio="${p.precio}">${p.nombre_producto} (Stock: ${p.stock})</option>`).join('') || ''}
            </select>
            <div class="invalid-feedback">Seleccione un producto</div>
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control cantidad-producto" name="cantidad[]" min="1" required>
            <div class="invalid-feedback">Ingrese una cantidad válida</div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm eliminar-producto"><i class="fas fa-trash"></i></button>
        </div>
    `;
    container.appendChild(fila);

    // Validaciones y eventos
    fila.querySelector('.select-producto').addEventListener('change', function () {
        validaSeleccion(this, "Seleccione un producto");
        actualizarTotal();
    });
    fila.querySelector('.cantidad-producto').addEventListener('input', function () {
        validaMinimo(this, 1, "Ingrese una cantidad válida");
        actualizarTotal();
    });
    fila.querySelector('.eliminar-producto').addEventListener('click', () => {
        fila.remove();
        actualizarTotal();
    });
}

function actualizarTotal() {
    let total = 0;
    document.querySelectorAll('.producto-fila').forEach(fila => {
        const select = fila.querySelector('.select-producto');
        const cantidad = parseInt(fila.querySelector('.cantidad-producto').value) || 0;
        const precio = parseFloat(select.selectedOptions[0]?.dataset.precio) || 0;
        total += precio * cantidad;
    });
    const costoEnvio = parseFloat(document.querySelector("#costo_envio").value) || 0;
    total += costoEnvio;
    document.querySelector("#total_calculado").value = total.toFixed(2);
}

function getBadgeClass(estado) {
    const clases = {
        'pendiente': 'bg-warning',
        'en camino': 'bg-primary',
        'en preparacioón': 'bg-primary',
        'entregado': 'bg-success',
        'cancelado': 'bg-danger'
    };
    return clases[estado] || 'bg-secondary';
}

function validaSeleccion(campo, mensajeError) {
    if (!campo.value) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling?.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        if (campo.nextElementSibling?.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }
}

function validaMinimo(campo, min, mensajeError) {
    const valor = parseFloat(campo.value);
    if (isNaN(valor) || valor < min) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling?.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        if (campo.nextElementSibling?.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }
}

function validarFormularioNuevo() {
    let valido = true;
    if (!validaSeleccion(document.getElementById('id_usuario'), "Seleccione un cliente")) valido = false;
    if (!validaSeleccion(document.getElementById('id_establecimiento'), "Seleccione un establecimiento")) valido = false;
    if (!validaSeleccion(document.getElementById('id_direccion'), "Seleccione una dirección")) valido = false;
    if (!validaSeleccion(document.getElementById('id_metodo_pago'), "Seleccione un método de pago")) valido = false;
    if (!validaMinimo(document.getElementById('costo_envio'), 0, "Ingrese un costo de envío válido")) valido = false;
    if (!validaSeleccion(document.getElementById('tiempo_estimado'), "Ingrese un tiempo estimado válido")) valido = false;

    document.querySelectorAll('.producto-fila').forEach(fila => {
        const select = fila.querySelector('.select-producto');
        const cantidadInput = fila.querySelector('.cantidad-producto');
        if (!validaSeleccion(select, "Seleccione un producto")) valido = false;
        if (!validaMinimo(cantidadInput, 1, "Ingrese una cantidad válida")) valido = false;
        if (select.value) {
            const producto = window.productosDisponibles.find(p => p.id_producto == select.value);
            if (producto && parseInt(cantidadInput.value) > producto.stock) {
                cantidadInput.classList.add("is-invalid");
                cantidadInput.nextElementSibling.textContent = `Stock disponible: ${producto.stock}`;
                valido = false;
            }
        }
    });

    return valido && document.querySelectorAll('.producto-fila').length > 0;
}

function validarFormularioEdicion() {
    let valido = true;
    if (!validaSeleccion(document.getElementById('editarEstado'), "Seleccione un estado")) valido = false;
    if (!validaSeleccion(document.getElementById('editarRepartidor'), "Seleccione un repartidor")) valido = false;
    return valido;
}