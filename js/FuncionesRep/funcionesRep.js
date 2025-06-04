document.addEventListener("DOMContentLoaded", function () {

    // Configuración de Mapbox solo para ubicación
    mapboxgl.accessToken = 'TU_TOKEN_MAPBOX'; // Reemplaza con tu token de Mapbox
    let mapUbicacion = null;

    // Cargar información del repartidor
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=getRepartidor&id_usuario=${id_usuario}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('disponibilidad').value = data.data.disponibilidad;
            // Perfil
            document.getElementById('perfil-nombre').textContent = data.data.nombre;
            document.getElementById('perfil-ap_paterno').textContent = data.data.ap_paterno;
            document.getElementById('perfil-ap_materno').textContent = data.data.ap_materno;
            document.getElementById('perfil-telefono').textContent = data.data.telefono;
            document.getElementById('perfil-correo').textContent = data.data.correo;
            document.getElementById('nombre').value = data.data.nombre;
            document.getElementById('ap_paterno').value = data.data.ap_paterno;
            document.getElementById('ap_materno').value = data.data.ap_materno;
            document.getElementById('telefono').value = data.data.telefono;
            document.getElementById('correo').value = data.data.correo;
            // Vehículo
            document.getElementById('vehiculo-tipo').textContent = data.data.tipo_vehiculo || 'No registrado';
            document.getElementById('vehiculo-marca').textContent = data.data.marca || 'No registrado';
            document.getElementById('vehiculo-modelo').textContent = data.data.modelo || 'No registrado';
            document.getElementById('vehiculo-color').textContent = data.data.color || 'No registrado';
            document.getElementById('vehiculo-placas').textContent = data.data.placas || 'No registrado';
            document.getElementById('tipo_vehiculo').value = data.data.tipo_vehiculo;
            document.getElementById('marca').value = data.data.marca;
            document.getElementById('modelo').value = data.data.modelo;
            document.getElementById('color').value = data.data.color;
            document.getElementById('placas').value = data.data.placas || '';
            cargarPedidos(id_repartidor);
            cargarNotificaciones(id_usuario);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje });
        }
    });

    // Simular notificación de nuevo pedido (puedes reemplazar con WebSockets para tiempo real)
    setInterval(() => {
        fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ope=checkNuevosPedidos&id_repartidor=${id_repartidor}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.nuevos > 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Nuevo Pedido',
                    text: `Tienes ${data.nuevos} nuevo(s) pedido(s) asignado(s).`,
                });
                cargarPedidos(id_repartidor);
            }
        });
    }, 30000); // Cada 30 segundos

    // Inicializar mapa de ubicación
    function inicializarMapas() {
        mapUbicacion = new mapboxgl.Map({
            container: 'map-ubicacion',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [-99.1332, 19.4326], // Ciudad de México por defecto
            zoom: 12
        });
    }
    inicializarMapas();
});

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

function mostrarSeccion(seccion) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById(`${seccion}-section`).classList.add('active');
    toggleSidebar();
    if (seccion === 'notificaciones') {
        cargarNotificaciones(id_usuario);
    } else if (seccion === 'perfil') {
        cancelarEdicionPerfil(); // Mostrar vista previa por defecto
    } else if (seccion === 'vehiculo') {
        cancelarEdicionVehiculo(); // Mostrar vista previa por defecto
    }
}

function cargarPedidos() {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    const filtro = document.getElementById('filtro-estado').value;
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=getPedidosPendientes&id_repartidor=${id_repartidor}&filtro=${filtro}`
    })
    .then(response => response.json())
    .then(data => {
        const lista = document.getElementById('pedidos-lista');
        lista.innerHTML = '';
        if (data.success && data.data.length > 0) {
            data.data.forEach(pedido => {
                const card = `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Pedido #${pedido.id_pedido}</h5>
                                <p><strong>Estado:</strong> ${pedido.estado}</p>
                                <p><strong>Dirección:</strong> ${pedido.calle} ${pedido.numero}, ${pedido.colonia}, ${pedido.ciudad}</p>
                                <p><strong>Total:</strong> $${pedido.total}</p>
                                <p><strong>Establecimiento:</strong> ${pedido.establecimiento}</p>
                                <button class="btn btn-primary me-2 mb-2" onclick="verDetalles(${pedido.id_pedido})">Ver Detalles</button>
                                ${pedido.estado === 'pendiente' ? `
                                    <button class="btn btn-success me-2 mb-2" onclick="aceptarRechazarPedido(${pedido.id_pedido}, 1, ${id_repartidor})">Aceptar</button>
                                    <button class="btn btn-danger me-2 mb-2" onclick="aceptarRechazarPedido(${pedido.id_pedido}, 0, ${id_repartidor})">Rechazar</button>
                                ` : ''}
                                ${pedido.estado === 'enviado' ? `
                                    <button class="btn btn-success me-2 mb-2" onclick="actualizarEstado(${pedido.id_pedido}, 'entregado')">Marcar Entregado</button>
                                    <button class="btn btn-danger mb-2" onclick="mostrarModalCancelacion(${pedido.id_pedido})">Cancelar</button>
                                ` : ''}
                            </div>
                        </div>
                    </div>`;
                lista.innerHTML += card;
            });
        } else {
            lista.innerHTML = '<p>No hay pedidos disponibles.</p>';
        }
    });
}

function verDetalles(id_pedido) {
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=getDetallesPedido&id_pedido=${id_pedido}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            const pedido = data.data[0];
            let productos = '<h6>Productos:</h6><ul>';
            data.data.forEach(item => {
                if (item.producto) {
                    productos += `<li>${item.cantidad} x ${item.producto} - $${item.subtotal}</li>`;
                }
            });
            productos += '</ul>';
            document.getElementById('detalles-pedido-content').innerHTML = `
                <p><strong>Pedido #:</strong> ${pedido.id_pedido}</p>
                <p><strong>Fecha:</strong> ${pedido.fecha_pedido}</p>
                <p><strong>Estado:</strong> ${pedido.estado}</p>
                <p><strong>Dirección:</strong> ${pedido.calle} ${pedido.numero}, ${pedido.colonia}, ${pedido.ciudad}, ${pedido.estado}, ${pedido.codigo_postal}</p>
                <p><strong>Referencias:</strong> ${pedido.referencias || 'Sin referencias'}</p>
                <p><strong>Establecimiento:</strong> ${pedido.establecimiento}</p>
                <p><strong>Método de Pago:</strong> ${pedido.nombre_metodo}</p>
                <p><strong>Total:</strong> $${pedido.total}</p>
                <p><strong>Notas:</strong> ${pedido.notas || 'Sin notas'}</p>
                ${productos}
            `;
            const modal = new bootstrap.Modal(document.getElementById('detallesPedidoModal'));
            modal.show();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se encontraron detalles del pedido.' });
        }
    });
}

function cargarHistorial() {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    const fecha_inicio = document.getElementById('fecha-inicio').value;
    const fecha_fin = document.getElementById('fecha-fin').value;
    let body = `ope=getHistorialEntregas&id_repartidor=${id_repartidor}`;
    if (fecha_inicio && fecha_fin) {
        body += `&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
    }
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(response => response.json())
    .then(data => {
        const lista = document.getElementById('historial-lista');
        lista.innerHTML = '';
        if (data.success && data.data.length > 0) {
            data.data.forEach(pedido => {
                const card = `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Pedido #${pedido.id_pedido}</h5>
                                <p><strong>Fecha:</strong> ${pedido.fecha_pedido}</p>
                                <p><strong>Dirección:</strong> ${pedido.calle} ${pedido.numero}, ${pedido.colonia}, ${pedido.ciudad}</p>
                                <p><strong>Total:</strong> $${pedido.total}</p>
                                <p><strong>Tiempo Real:</strong> ${pedido.tiempo_real || 'No registrado'}</p>
                                <p><strong>Establecimiento:</strong> ${pedido.establecimiento}</p>
                            </div>
                        </div>
                    </div>`;
                lista.innerHTML += card;
            });
        } else {
            lista.innerHTML = '<p>No hay entregas en el historial.</p>';
        }
    });
}

function actualizarDisponibilidad() {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    const disponibilidad = document.getElementById('disponibilidad').value;
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=updateDisponibilidad&id_repartidor=${id_repartidor}&disponibilidad=${disponibilidad}`
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
        if (data.success && disponibilidad === 'no disponible') {
            cargarPedidos(id_repartidor);
        }
    });
}

function aceptarRechazarPedido(id_pedido, aceptar, id_repartidor) {
    let body = `ope=aceptarRechazarPedido&id_pedido=${id_pedido}&aceptar=${aceptar}&id_repartidor=${id_repartidor}`;
    if (!aceptar) {
        const motivo = prompt('Motivo del rechazo:');
        if (!motivo) return;
        body += `&motivo=${encodeURIComponent(motivo)}`;
    }
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
        if (data.success) cargarPedidos(id_repartidor);
    });
}

let pedidoCancelacionId = null;

function mostrarModalCancelacion(id_pedido) {
    pedidoCancelacionId = id_pedido;
    document.getElementById('motivo_cancelacion').value = '';
    document.getElementById('motivo_cancelacion').classList.remove('is-invalid');
    const modal = new bootstrap.Modal(document.getElementById('cancelarPedidoModal'));
    modal.show();
}

function confirmarCancelacion() {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    const motivo = document.getElementById('motivo_cancelacion');
    if (validaLargo(motivo, 1)) {
        fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ope=cancelarPedido&id_pedido=${pedidoCancelacionId}&id_repartidor=${id_repartidor}&motivo=${encodeURIComponent(motivo.value)}`
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('cancelarPedidoModal'));
                modal.hide();
                cargarPedidos(id_repartidor);
            }
        });
    }
}

function actualizarEstado(id_pedido, estado) {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=updateEstadoPedido&id_pedido=${id_pedido}&estado=${estado}`
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
        if (data.success) cargarPedidos(id_repartidor);
    });
}

function mostrarFormularioPerfil() {
    document.getElementById('perfil-vista').style.display = 'none';
    document.getElementById('formularioPerfil').style.display = 'block';
}

function cancelarEdicionPerfil() {
    document.getElementById('perfil-vista').style.display = 'block';
    document.getElementById('formularioPerfil').style.display = 'none';
}

function mostrarFormularioVehiculo() {
    document.getElementById('vehiculo-vista').style.display = 'none';
    document.getElementById('formularioVehiculo').style.display = 'block';
}

function cancelarEdicionVehiculo() {
    document.getElementById('vehiculo-vista').style.display = 'block';
    document.getElementById('formularioVehiculo').style.display = 'none';
}

function guardarPerfil() {
    const id_usuario = 3; // Simulado, debe venir de la sesión
    const nombre = document.getElementById('nombre');
    const ap_paterno = document.getElementById('ap_paterno');
    const ap_materno = document.getElementById('ap_materno');
    const telefono = document.getElementById('telefono');
    const correo = document.getElementById('correo');
    if (validaLargo(nombre, 1) && validaLargo(ap_paterno, 1) && validaLargo(ap_materno, 1) && validaLargo(telefono, 10) && validaCorreo(correo)) {
        fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ope=updatePerfil&id_usuario=${id_usuario}&nombre=${encodeURIComponent(nombre.value)}&ap_paterno=${encodeURIComponent(ap_paterno.value)}&ap_materno=${encodeURIComponent(ap_materno.value)}&telefono=${encodeURIComponent(telefono.value)}&correo=${encodeURIComponent(correo.value)}`
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
            if (data.success) {
                document.getElementById('perfil-nombre').textContent = nombre.value;
                document.getElementById('perfil-ap_paterno').textContent = ap_paterno.value;
                document.getElementById('perfil-ap_materno').textContent = ap_materno.value;
                document.getElementById('perfil-telefono').textContent = telefono.value;
                document.getElementById('perfil-correo').textContent = correo.value;
                cancelarEdicionPerfil();
            }
        });
    }
}

function guardarVehiculo() {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    const tipo_vehiculo = document.getElementById('tipo_vehiculo');
    const marca = document.getElementById('marca');
    const modelo = document.getElementById('modelo');
    const color = document.getElementById('color');
    const placas = document.getElementById('placas');
    if (validaLargo(marca, 1) && validaLargo(modelo, 1) && validaLargo(color, 1)) {
        fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ope=updateVehiculo&id_repartidor=${id_repartidor}&tipo_vehiculo=${tipo_vehiculo.value}&marca=${encodeURIComponent(marca.value)}&modelo=${encodeURIComponent(modelo.value)}&color=${encodeURIComponent(color.value)}&placas=${encodeURIComponent(placas.value)}`
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
            if (data.success) {
                document.getElementById('vehiculo-tipo').textContent = tipo_vehiculo.value || 'No registrado';
                document.getElementById('vehiculo-marca').textContent = marca.value || 'No registrado';
                document.getElementById('vehiculo-modelo').textContent = modelo.value || 'No registrado';
                document.getElementById('vehiculo-color').textContent = color.value || 'No registrado';
                document.getElementById('vehiculo-placas').textContent = placas.value || 'No registrado';
                cancelarEdicionVehiculo();
            }
        });
    }
}

function actualizarUbicacion() {
    const id_repartidor = 1; // Simulado, debe venir de la sesión
    const latitud = document.getElementById('latitud');
    const longitud = document.getElementById('longitud');
    if (validaLargo(latitud, 1) && validaLargo(longitud, 1)) {
        fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ope=updateUbicacion&id_repartidor=${id_repartidor}&latitud=${latitud.value}&longitud=${longitud.value}`
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
            if (data.success) {
                mapUbicacion.setCenter([longitud.value, latitud.value]);
                new mapboxgl.Marker()
                    .setLngLat([longitud.value, latitud.value])
                    .addTo(mapUbicacion);
            }
        });
    }
}

function cargarNotificaciones(id_usuario) {
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=getNotificaciones&id_usuario=${id_usuario}`
    })
    .then(response => response.json())
    .then(data => {
        const lista = document.getElementById('notificaciones-lista').querySelector('.card-body');
        lista.innerHTML = '';
        if (data.success && data.data.length > 0) {
            data.data.forEach(notif => {
                const item = document.createElement('div');
                item.className = `notification-item ${notif.leido ? '' : 'unread'}`;
                item.innerHTML = `
                    <span>${notif.mensaje} (${notif.fecha_envio})</span>
                    ${notif.leido ? '' : `<button class="btn btn-primary btn-sm" onclick="marcarNotificacionLeida(${notif.id_notificacion})">Marcar como Leído</button>`}
                `;
                lista.appendChild(item);
            });
        } else {
            lista.innerHTML = '<p>No hay notificaciones.</p>';
        }
    });
}

function marcarNotificacionLeida(id_notificacion) {
    const id_usuario = 3; // Simulado, debe venir de la sesión
    fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ope=marcarNotificacionLeida&id_notificacion=${id_notificacion}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarNotificaciones(id_usuario);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje });
        }
    });
}

function reportarIncidencia() {
    const id_pedido = document.getElementById('id_pedido_incidencia');
    const mensaje = document.getElementById('mensaje_incidencia');
    if (validaLargo(id_pedido, 1) && validaLargo(mensaje, 1)) {
        fetch('http://localhost/FORY-FINAL/controladores/ControladorRepartidor/controlador_rep.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ope=reportarIncidencia&id_pedido=${id_pedido.value}&mensaje=${encodeURIComponent(mensaje.value)}`
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Éxito' : 'Error', text: data.mensaje });
            if (data.success) mostrarSeccion('pedidos');
        });
    }
}

function validaLargo(campo, largo) {
    const valor = typeof campo === 'string' ? campo : campo.value;
    if (valor.length < largo) {
        if (typeof campo !== 'string') {
            campo.classList.add("is-invalid");
            campo.classList.remove("is-valid");
        }
        return false;
    } else {
        if (typeof campo !== 'string') {
            campo.classList.remove("is-invalid");
            campo.classList.add("is-valid");
        }
        return true;
    }
}

function validaCorreo(campo) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const valor = typeof campo === 'string' ? campo : campo.value;
    if (regex.test(valor) && valor.length >= 5) {
        if (typeof campo !== 'string') {
            campo.classList.add("is-valid");
            campo.classList.remove("is-invalid");
        }
        return true;
    } else {
        if (typeof campo !== 'string') {
            campo.classList.add("is-invalid");
            campo.classList.remove("is-valid");
        }
        return false;
    }
}