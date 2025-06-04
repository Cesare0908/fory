document.addEventListener("DOMContentLoaded", () => {
    cargarRoles();

    // Configuración de la tabla de usuarios
    if (document.querySelector("#tablaUsuarios")) {
        const modalEditarUsuario = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
        const modalNuevoUsuario = new bootstrap.Modal(document.getElementById('modalNuevoUsuario'));
        const modalDetallesUsuario = new bootstrap.Modal(document.getElementById('modalDetallesUsuario'));

        const grid = new gridjs.Grid({
            columns: [
                { name: 'ID', width: '10%' },
                { name: 'Nombre', width: '20%' },
                { name: 'Apellido Paterno', width: '20%' },
                { name: 'Apellido Materno', width: '20%' },
                { name: 'Rol', width: '15%' },
                {
                    name: "Acciones",
                    formatter: (_, rows) => gridjs.html(`
                        <button class="btn btn-sm action-btn details-btn ver-detalles" 
                            data-id="${rows.cells[0].data}"
                            title="Ver detalles del usuario">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn edit-btn editar" 
                            data-id="${rows.cells[0].data}" 
                            title="Editar usuario">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm action-btn delete-btn borrar" 
                            data-id="${rows.cells[0].data}"
                            data-nombre="${rows.cells[1].data}"
                            title="Eliminar usuario">
                            <i class="fas fa-trash"></i>
                        </button>
                    `),
                    sort: false,
                    width: '15%'
                }
            ],
            pagination: true,
            search: true,
            sort: true,
            resizable: true,
            server: {
                url: "http://localhost/fory-final/controladores/controladorUsu.php?ope1=ListaUsuarios",
                then: (data) => data.results.map((usuario) => [
                    usuario.id_usuario,
                    usuario.nombre,
                    usuario.ap_paterno,
                    usuario.ap_materno,
                    usuario.tipo_rol
                ])
            },
            language: {
                search: { placeholder: '🔎 Escribe para buscar...' },
                pagination: {
                    previous: '⬅️',
                    next: '➡️',
                    navigate: (page, pages) => `Página ${page} de ${pages}`,
                    showing: '😁 Mostrando del',
                    of: 'de',
                    to: 'al',
                    results: 'registros',
                },
                loading: 'Cargando...',
                noRecordsFound: 'Sin coincidencias encontradas.',
                error: 'Ocurrió un error al obtener los datos.'
            }
        }).render(document.querySelector("#tablaUsuarios"));

        // Actualizar lista de usuarios
        document.querySelector("#actListadoUsuarios").addEventListener("click", (event) => {
            event.preventDefault();
            grid.updateConfig({
                server: {
                    url: "http://localhost/fory-final/controladores/controladorUsu.php?ope1=ListaUsuarios",
                    then: (data) => data.results.map((usuario) => [
                        usuario.id_usuario,
                        usuario.nombre,
                        usuario.ap_paterno,
                        usuario.ap_materno,
                        usuario.tipo_rol
                    ])
                }
            }).forceRender();
        });

        // Acciones en la tabla
        document.querySelector("#tablaUsuarios").addEventListener("click", (event) => {
            event.preventDefault();
            let ele = event.target.closest('button');

            // Ver detalles del usuario
            if (ele?.classList.contains("ver-detalles")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarUsuario");
                info.append("id", id); 

                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/fory-final/controladores/controladorUsu.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#detalleID").textContent = xhr.response.id_usuario;
                        document.querySelector("#detalleNombre").textContent = xhr.response.nombre;
                        document.querySelector("#detalleApPaterno").textContent = xhr.response.ap_paterno;
                        document.querySelector("#detalleApMaterno").textContent = xhr.response.ap_materno;
                        document.querySelector("#detalleCorreo").textContent = xhr.response.correo;
                        document.querySelector("#detalleTelefono").textContent = xhr.response.telefono;
                        document.querySelector("#detalleRol").textContent = xhr.response.tipo_rol || 'No especificado';
                        modalDetallesUsuario.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el usuario',
                        });
                    }  
                };
            }

            // Editar usuario
            if (ele?.classList.contains("editar")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarUsuario");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/fory-final/controladores/controladorUsu.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#editarIDUsuario").value = xhr.response.id_usuario;
                        document.querySelector("#editarNombre").value = xhr.response.nombre;
                        document.querySelector("#editarApPaterno").value = xhr.response.ap_paterno;
                        document.querySelector("#editarApMaterno").value = xhr.response.ap_materno;
                        document.querySelector("#editarCorreo").value = xhr.response.correo;
                        document.querySelector("#editarTelefono").value = xhr.response.telefono;
                        document.querySelector("#editarRol").value = xhr.response.id_rol;
                        modalEditarUsuario.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el usuario',
                        });
                    }
                };
            }

            // Eliminar usuario
            if (ele?.classList.contains("borrar")) {
                let id = ele.getAttribute("data-id");
                let nombre = ele.getAttribute("data-nombre");

                // Verificar si el usuario es Administrador
                let info = new FormData();
                info.append("ope", "buscarUsuario");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/fory-final/controladores/controladorUsu.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        if (xhr.response.id_rol == 1) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Acción Prohibida',
                                text: 'No se pueden eliminar usuarios con rol de Administrador desde la interfaz.',
                            });
                            return;
                        }

                        Swal.fire({
                            title: "¿Está Usted Seguro?",
                            text: "Intenta borrar el usuario: " + nombre,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Sí, borrarlo!",
                            cancelButtonText: "Cancelar"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                let deleteInfo = new FormData();
                                deleteInfo.append("ope", "borrarUSU");
                                deleteInfo.append("id", id);

                                let deleteXhr = new XMLHttpRequest();
                                deleteXhr.open('POST', 'http://localhost/fory-final/controladores/controladorUsu.php');
                                deleteXhr.send(deleteInfo);

                                deleteXhr.onload = function () {
                                    let response = JSON.parse(deleteXhr.responseText);
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Borrado',
                                            text: response.message,
                                        });
                                        document.querySelector("#actListadoUsuarios").click();
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message || 'No se pudo borrar el usuario',
                                        });
                                    }
                                };
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el usuario',
                        });
                    }
                };
            }
        });

        // Agregar nuevo usuario
        document.querySelector("#btnAgregarUsuario").addEventListener("click", function () {
            modalNuevoUsuario.show();
            cargarRoles();
            document.querySelector("#formNuevoUsuario").reset();
            document.querySelectorAll('#formNuevoUsuario .form-control').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.textContent = '';
                }
            });
        });

        // Guardar nuevo usuario con validación
        document.querySelector("#btnGuardarUsuario").addEventListener("click", function (event) {
            event.preventDefault();

            if (!validarFormularioUsuario()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formNuevoUsuario = document.querySelector("#formNuevoUsuario");
            let formData = new FormData(formNuevoUsuario);
            formData.append("ope", "guardarUSU");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "http://localhost/fory-final/controladores/controladorUsu.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: response.mensaje,
                    }).then(() => {
                        formNuevoUsuario.reset();
                        modalNuevoUsuario.hide();
                        document.querySelector("#actListadoUsuarios").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo guardar el usuario',
                    });
                }
            };
            xhr.send(formData);
        });

        // Editar usuario con validación
        document.querySelector("#btnEditarUsuario").addEventListener("click", function (event) {
            event.preventDefault();

            if (!validarFormularioEdicion()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formEditarUsuario = document.querySelector("#formEditarUsuario");
            let formData = new FormData(formEditarUsuario);
            formData.append("ope", "editarUSU");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "  http://localhost/fory-final/controladores/controladorUsu.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: response.mensaje,
                    }).then(() => {
                        modalEditarUsuario.hide();
                        document.querySelector("#actListadoUsuarios").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo actualizar el usuario',
                    });
                }
            };
            xhr.send(formData);
        });

        // Validaciones en tiempo real
        document.getElementById('nombre')?.addEventListener('input', function () {
            validaLargo(this, 3, "El nombre debe tener al menos 3 caracteres");
        });
        document.getElementById('ap_paterno')?.addEventListener('input', function () {
            validaLargo(this, 3, "El apellido paterno debe tener al menos 3 caracteres");
        });
        document.getElementById('ap_materno')?.addEventListener('input', function () {
            validaLargo(this, 3, "El apellido materno debe tener al menos 3 caracteres");
        });
        document.getElementById('correo')?.addEventListener('input', function () {
            validaCorreo(this, "Ingrese un correo válido");
        });
        document.getElementById('contraseña')?.addEventListener('input', function () {
            validaLargo(this, 6, "La contraseña debe tener al menos 6 caracteres");
        });
        document.getElementById('telefono')?.addEventListener('input', function () {
            validaTelefono(this, "Ingrese un teléfono válido (10 dígitos)");
        });
        document.getElementById('id_rol')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un rol");
        });

        document.getElementById('editarNombre')?.addEventListener('input', function () {
            validaLargo(this, 3, "El nombre debe tener al menos 3 caracteres");
        });
        document.getElementById('editarApPaterno')?.addEventListener('input', function () {
            validaLargo(this, 3, "El apellido paterno debe tener al menos 3 caracteres");
        });
        document.getElementById('editarApMaterno')?.addEventListener('input', function () {
            validaLargo(this, 3, "El apellido materno debe tener al menos 3 caracteres");
        });
        document.getElementById('editarCorreo')?.addEventListener('input', function () {
            validaCorreo(this, "Ingrese un correo válido");
        });
        document.getElementById('editarTelefono')?.addEventListener('input', function () {
            validaTelefono(this, "Ingrese un teléfono válido (10 dígitos)");
        });
        document.getElementById('editarRol')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione un rol");
        });
    }
});

function cargarRoles() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost/fory-final/controladores/controladorUsu.php?ope1=ListaRoles');
    xhr.onload = function () {
        if (xhr.status === 200) {
            let roles = JSON.parse(xhr.responseText);
            let selectNuevo = document.getElementById('id_rol');
            let selectEditar = document.getElementById('editarRol');

            selectNuevo.innerHTML = '<option value="">Seleccione un rol</option>';
            selectEditar.innerHTML = '<option value="">Seleccione un rol</option>';

            roles.forEach(rol => {
                let option = document.createElement('option');
                option.value = rol.id_rol;
                option.textContent = rol.tipo_rol;
                selectNuevo.appendChild(option.cloneNode(true));
                selectEditar.appendChild(option.cloneNode(true));
            });
        }
    };
    xhr.send();
}

// Función de validación de largo mínimo
function validaLargo(campo, min, mensajeError) {
    if (campo.value.length < min) {
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

// Validación de correo
function validaCorreo(campo, mensajeError) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(campo.value)) {
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

// Validación de teléfono
function validaTelefono(campo, mensajeError) {
    const regex = /^\d{10}$/;
    if (!regex.test(campo.value)) {
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

// Validación de selección
function validaSeleccion(campo, mensajeError) {
    if (campo.value === "") {
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

// Validación del formulario de nuevo usuario
function validarFormularioUsuario() {
    let valido = true;
    if (!validaLargo(document.getElementById('nombre'), 3, "El nombre debe tener al menos 3 caracteres")) valido = false;
    if (!validaLargo(document.getElementById('ap_paterno'), 3, "El apellido paterno debe tener al menos 3 caracteres")) valido = false;
    if (!validaLargo(document.getElementById('ap_materno'), 3, "El apellido materno debe tener al menos 3 caracteres")) valido = false;
    if (!validaCorreo(document.getElementById('correo'), "Ingrese un correo válido")) valido = false;
    if (!validaLargo(document.getElementById('contraseña'), 6, "La contraseña debe tener al menos 6 caracteres")) valido = false;
    if (!validaTelefono(document.getElementById('telefono'), "Ingrese un teléfono válido (10 dígitos)")) valido = false;
    if (!validaSeleccion(document.getElementById('id_rol'), "Seleccione un rol")) valido = false;
    return valido;
}

// Validación del formulario de edición
function validarFormularioEdicion() {
    let valido = true;
    if (!validaLargo(document.getElementById('editarNombre'), 3, "El nombre debe tener al menos 3 caracteres")) valido = false;
    if (!validaLargo(document.getElementById('editarApPaterno'), 3, "El apellido paterno debe tener al menos 3 caracteres")) valido = false;
    if (!validaLargo(document.getElementById('editarApMaterno'), 3, "El apellido materno debe tener al menos 3 caracteres")) valido = false;
    if (!validaCorreo(document.getElementById('editarCorreo'), "Ingrese un correo válido")) valido = false;
    if (!validaTelefono(document.getElementById('editarTelefono'), "Ingrese un teléfono válido (10 dígitos)")) valido = false;
    if (!validaSeleccion(document.getElementById('editarRol'), "Seleccione un rol")) valido = false;
    return valido;
}