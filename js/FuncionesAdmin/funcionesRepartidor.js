document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios();
  
    // Configuración de la tabla de repartidores
    if (document.querySelector("#tablaRepartidores")) {
        const modalEditarRepartidor = new bootstrap.Modal(document.getElementById('modalEditarRepartidor'));
        const modalNuevoRepartidor = new bootstrap.Modal(document.getElementById('modalNuevoRepartidor'));
        const modalDetallesVehiculo = new bootstrap.Modal(document.getElementById('modalDetallesVehiculo'));

        const grid = new gridjs.Grid({
            columns: [
                { name: 'ID', width: '10%' },
                { name: 'Nombre', width: '30%' },
                { name: 'Disponibilidad', width: '20%' },
                { 
                    name: 'Vehículo', 
                    width: '20%',
                    formatter: (cell) => cell || 'Sin vehículo'
                },
                {
                    name: "Acciones",
                    formatter: (_, rows) => gridjs.html(`
                        <button class="btn btn-sm action-btn details-btn ver-detalles" 
                            data-id="${rows.cells[0].data}"
                            title="Ver detalles del vehículo">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn edit-btn editar" 
                            data-id="${rows.cells[0].data}" 
                            data-id_usuario="${rows.cells[1].data.split(' - ')[0]}"
                            title="Editar repartidor">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm action-btn delete-btn borrar" 
                            data-id="${rows.cells[0].data}"
                            data-nombre="${rows.cells[1].data}"
                            title="Eliminar repartidor">
                            <i class="fas fa-trash"></i>
                        </button>
                    `),
                    sort: false,
                    width: '20%'
                }
            ],
            pagination: true,
            search: true,
            sort: true,
            resizable: true,
            server: {
                url: "http://localhost/fory-final/controladores/controladorRep.php?ope1=ListaRepartidores",
                then: (data) => data.results.map((repartidor) => [
                    repartidor.id_repartidor,
                    `${repartidor.id_usuario} - ${repartidor.nombre_usuario}`,
                    repartidor.disponibilidad,
                    repartidor.tipo_vehiculo || ''
                ])
            },
            language: {
                search: { placeholder: '🔎 Buscar repartidor...' },
                pagination: {
                    previous: '⬅️',
                    next: '➡️',
                    navigate: (page, pages) => `Página ${page} de ${pages}`,
                    showing: 'Mostrando',
                    of: 'de',
                    to: 'a',
                    results: 'registros',
                },
                loading: 'Cargando...',
                noRecordsFound: 'No se encontraron repartidores.',
                error: 'Error al cargar los datos.'
            }
        }).render(document.querySelector("#tablaRepartidores"));

        // Actualizar lista de repartidores
        document.querySelector("#actListadoRepartidores").addEventListener("click", (event) => {
            event.preventDefault();
            grid.forceRender();
        });

        // Acciones en la tabla
        document.querySelector("#tablaRepartidores").addEventListener("click", (event) => {
            event.preventDefault();
            let ele = event.target.closest('button');

            // Ver detalles del vehículo
            if (ele?.classList.contains("ver-detalles")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarRepartidor");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/fory-final/controladores/controladorRep.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#detalleTipoVehiculo").textContent = xhr.response.tipo_vehiculo || 'No especificado';
                        document.querySelector("#detalleMarca").textContent = xhr.response.marca || 'No especificado';
                        document.querySelector("#detalleModelo").textContent = xhr.response.modelo || 'No especificado';
                        document.querySelector("#detalleColor").textContent = xhr.response.color || 'No especificado';
                        document.querySelector("#detallePlacas").textContent = xhr.response.placas || 'No especificado';
                        modalDetallesVehiculo.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró información del vehículo',
                        });
                    }
                };
            }

            // Editar repartidor
            if (ele?.classList.contains("editar")) {
                let id = ele.getAttribute("data-id");
                let id_usuario = ele.getAttribute("data-id_usuario");
                document.querySelector("#editarIDRepartidor").value = id;
                document.querySelector("#editarUsuario").value = id_usuario;
                modalEditarRepartidor.show();
            }

            // Eliminar repartidor
            if (ele?.classList.contains("borrar")) {
                Swal.fire({
                    title: "¿Está seguro?",
                    text: "Va a eliminar al repartidor: " + ele.getAttribute("data-nombre"),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        let id = ele.getAttribute("data-id");
                        let info = new FormData();
                        info.append("ope", "borrarREP");
                        info.append("id", id);

                        let xhr = new XMLHttpRequest();
                        xhr.open('POST', 'http://localhost/fory-final/controladores/controladorRep.php');
                        xhr.send(info);

                        xhr.onload = function () {
                            let response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message,
                                });
                                document.querySelector("#actListadoRepartidores").click();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo eliminar el repartidor',
                                });
                            }
                        };
                    }
                });
            }
        });

        // Agregar nuevo repartidor
        document.querySelector("#btnAgregarRepartidor").addEventListener("click", function () {
            modalNuevoRepartidor.show();
            cargarUsuarios();
            document.querySelector("#formNuevoRepartidor").reset();
            document.querySelectorAll('#formNuevoRepartidor .form-control').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.textContent = '';
                }
            });
        });

        // Guardar nuevo repartidor
        document.querySelector("#btnGuardarRepartidor").addEventListener("click", function (event) {
            event.preventDefault();

            if (!validarFormularioRepartidor()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formNuevoRepartidor = document.querySelector("#formNuevoRepartidor");
            let formData = new FormData(formNuevoRepartidor);
            formData.append("ope", "guardarREP");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "http://localhost/fory-final/controladores/controladorRep.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: response.mensaje,
                    }).then(() => {
                        formNuevoRepartidor.reset();
                        modalNuevoRepartidor.hide();
                        document.querySelector("#actListadoRepartidores").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo guardar el repartidor',
                    });
                }
            };
            xhr.send(formData);
        });

        // Editar repartidor
        document.querySelector("#btnEditarRepartidor").addEventListener("click", function (event) {
            event.preventDefault();

            if (!validarFormularioEdicion()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formEditarRepartidor = document.querySelector("#formEditarRepartidor");
            let formData = new FormData(formEditarRepartidor);
            formData.append("ope", "editarREP");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "  http://localhost/fory-final/controladores/controladorRep.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: response.mensaje,
                    }).then(() => {
                        modalEditarRepartidor.hide();
                        document.querySelector("#actListadoRepartidores").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo actualizar el repartidor',
                    });
                }
            };
            xhr.send(formData);
        });

        // Validaciones en tiempo real
        document.getElementById('id_usuario')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un usuario");
        });
        document.getElementById('disponibilidad')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione la disponibilidad");
        });
        document.getElementById('editarUsuario')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un usuario");
        });
    }
});

function cargarUsuarios() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost/fory-final/controladores/controladorRep.php?ope1=ListaUsuarios');
    xhr.onload = function () {
        if (xhr.status === 200) {
            let usuarios = JSON.parse(xhr.responseText).results;
            let selectNuevo = document.getElementById('id_usuario');
            let selectEditar = document.getElementById('editarUsuario');

            selectNuevo.innerHTML = '<option value="">Seleccione un usuario</option>';
            selectEditar.innerHTML = '<option value="">Seleccione un usuario</option>';

            usuarios.forEach(usuario => {
                let option = document.createElement('option');
                option.value = usuario.id_usuario;
                option.textContent = `${usuario.id_usuario} - ${usuario.nombre} ${usuario.ap_paterno} ${usuario.ap_materno}`;
                selectNuevo.appendChild(option.cloneNode(true));
                selectEditar.appendChild(option.cloneNode(true));
            });
        }
    };
    xhr.send();
}

// Validación de selección
function validaSeleccion(campo, mensajeError) {
    if (!campo.value) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }
}

// Validación del formulario de nuevo repartidor
function validarFormularioRepartidor() {
    let valido = true;
    if (!validaSeleccion(document.getElementById('id_usuario'), "Seleccione un usuario")) valido = false;
    if (!validaSeleccion(document.getElementById('disponibilidad'), "Seleccione la disponibilidad")) valido = false;
    return valido;
}

// Validación del formulario de edición
function validarFormularioEdicion() {
    let valido = true;
    if (!validaSeleccion(document.getElementById('editarUsuario'), "Seleccione un usuario")) valido = false;
    return valido;
}