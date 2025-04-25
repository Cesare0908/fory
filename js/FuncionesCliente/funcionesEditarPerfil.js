document.addEventListener("DOMContentLoaded", () => {
    const modalEditarPerfil = new bootstrap.Modal(document.getElementById('modalEditarPerfil'));
    const modalEditarDireccion = new bootstrap.Modal(document.getElementById('modalEditarDireccion'));

    // Cargar datos del perfil y direcciones al iniciar
    cargarPerfil();
    cargarDirecciones();

    // Función para cargar el perfil del usuario
    function cargarPerfil() {
        fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php?ope=obtenerPerfil")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector("#perfilID").textContent = data.id_usuario;
                    document.querySelector("#perfilNombre").textContent = data.nombre;
                    document.querySelector("#perfilApPaterno").textContent = data.ap_paterno;
                    document.querySelector("#perfilApMaterno").textContent = data.ap_materno;
                    document.querySelector("#perfilCorreo").textContent = data.correo;
                    document.querySelector("#perfilTelefono").textContent = data.telefono;
                    document.querySelector("#perfilRol").textContent = data.tipo_rol || 'No especificado';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'No se encontró el usuario',
                    });
                }
            });
    }

    // Función para cargar las direcciones del usuario
    function cargarDirecciones() {
        fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php?ope=obtenerDirecciones")
            .then(response => response.json())
            .then(data => {
                const listaDirecciones = document.querySelector("#listaDirecciones");
                listaDirecciones.innerHTML = '';

                if (data.success && data.direcciones.length > 0) {
                    data.direcciones.forEach(direccion => {
                        const direccionDiv = document.createElement('div');
                        direccionDiv.classList.add('card', 'mb-2', 'p-3');
                        direccionDiv.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="mb-0"><strong>Dirección:</strong> ${direccion.calle} ${direccion.numero}, ${direccion.colonia}, ${direccion.ciudad}, ${direccion.estado}, CP ${direccion.codigo_postal}</p>
                                <div>
                                    <button class="btn btn-custom-warning me-1 editar-direccion" data-id="${direccion.id_direccion}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-custom-danger eliminar-direccion" data-id="${direccion.id_direccion}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        listaDirecciones.appendChild(direccionDiv);
                    });
                } else {
                    listaDirecciones.innerHTML = '<p>No tienes direcciones registradas.</p>';
                }
            });
    }

    // Mostrar modal para editar perfil
    document.querySelector("#editarPerfilBtn").addEventListener("click", () => {
        fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php?ope=obtenerPerfil")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector("#editarIDUsuario").value = data.id_usuario;
                    document.querySelector("#editarNombre").value = data.nombre;
                    document.querySelector("#editarApPaterno").value = data.ap_paterno;
                    document.querySelector("#editarApMaterno").value = data.ap_materno;
                    document.querySelector("#editarCorreo").value = data.correo;
                    document.querySelector("#editarTelefono").value = data.telefono;
                    document.querySelector("#editarContrasena").value = '';
                    document.querySelector("#confirmarContrasena").value = '';
                    modalEditarPerfil.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'No se encontró el usuario',
                    });
                }
            });
    });

    // Guardar cambios en el perfil
    document.querySelector("#btnGuardarCambios").addEventListener("click", (event) => {
        event.preventDefault();

        if (!validarFormularioEdicion()) {
            Swal.fire({
                icon: 'error',
                title: 'Error en el formulario',
                text: 'Por favor complete todos los campos requeridos correctamente',
            });
            return;
        }

        let formEditarPerfil = document.querySelector("#formEditarPerfil");
        let formData = new FormData(formEditarPerfil);
        formData.append("ope", "actualizarPerfil");

        fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Actualizado',
                    text: data.mensaje,
                }).then(() => {
                    modalEditarPerfil.hide();
                    cargarPerfil();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje || 'No se pudo actualizar el perfil',
                });
            }
        });
    });

    // Manejar edición y eliminación de direcciones
    document.querySelector("#listaDirecciones").addEventListener("click", (event) => {
        const editarBtn = event.target.closest('.editar-direccion');
        const eliminarBtn = event.target.closest('.eliminar-direccion');

        // Editar dirección
        if (editarBtn) {
            const idDireccion = editarBtn.getAttribute('data-id');
            fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php?ope=obtenerDirecciones")
                .then(response => response.json())
                .then(data => {
                    const direccion = data.direcciones.find(d => d.id_direccion == idDireccion);
                    if (direccion) {
                        document.querySelector("#editarIdDireccion").value = direccion.id_direccion;
                        document.querySelector("#editarCalle").value = direccion.calle;
                        document.querySelector("#editarNumero").value = direccion.numero;
                        document.querySelector("#editarColonia").value = direccion.colonia;
                        document.querySelector("#editarCiudad").value = direccion.ciudad;
                        document.querySelector("#editarEstado").value = direccion.estado;
                        document.querySelector("#editarCodigoPostal").value = direccion.codigo_postal;
                        modalEditarDireccion.show();
                    }
                });
        }

        // Eliminar dirección
        if (eliminarBtn) {
            const idDireccion = eliminarBtn.getAttribute('data-id');
            Swal.fire({
                title: "¿Está seguro?",
                text: "Se eliminará esta dirección permanentemente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = new FormData();
                    formData.append("ope", "eliminarDireccion");
                    formData.append("id_direccion", idDireccion);

                    fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminada',
                                text: data.mensaje,
                            }).then(() => {
                                cargarDirecciones();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje || 'No se pudo eliminar la dirección',
                            });
                        }
                    });
                }
            });
        }
    });

    // Guardar cambios en la dirección
    document.querySelector("#btnGuardarDireccion").addEventListener("click", (event) => {
        event.preventDefault();

        if (!validarFormularioDireccion()) {
            Swal.fire({
                icon: 'error',
                title: 'Error en el formulario',
                text: 'Por favor complete todos los campos requeridos correctamente',
            });
            return;
        }

        let formEditarDireccion = document.querySelector("#formEditarDireccion");
        let formData = new FormData(formEditarDireccion);
        formData.append("ope", "actualizarDireccion");

        fetch("../../controladores/ControladorCliente/controladorEditarPerfil.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Actualizada',
                    text: data.mensaje,
                }).then(() => {
                    modalEditarDireccion.hide();
                    cargarDirecciones();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje || 'No se pudo actualizar la dirección',
                });
            }
        });
    });

    // Validaciones en tiempo real
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
    document.getElementById('editarContrasena')?.addEventListener('input', function () {
        validaContrasena(this, "La contraseña debe tener al menos 6 caracteres");
        validaConfirmarContrasena(document.getElementById('confirmarContrasena'), "Las contraseñas no coinciden");
    });
    document.getElementById('confirmarContrasena')?.addEventListener('input', function () {
        validaConfirmarContrasena(this, "Las contraseñas no coinciden");
    });

    // Validaciones para el formulario de dirección
    document.getElementById('editarCalle')?.addEventListener('input', function () {
        validaLargo(this, 3, "La calle debe tener al menos 3 caracteres");
    });
    document.getElementById('editarNumero')?.addEventListener('input', function () {
        validaNumero(this, "Ingrese un número válido");
    });
    document.getElementById('editarColonia')?.addEventListener('input', function () {
        validaLargo(this, 3, "La colonia debe tener al menos 3 caracteres");
    });
    document.getElementById('editarCiudad')?.addEventListener('input', function () {
        validaLargo(this, 3, "La ciudad debe tener al menos 3 caracteres");
    });
    document.getElementById('editarEstado')?.addEventListener('input', function () {
        validaLargo(this, 3, "El estado debe tener al menos 3 caracteres");
    });
    document.getElementById('editarCodigoPostal')?.addEventListener('input', function () {
        validaCodigoPostal(this, "El código postal debe tener 5 dígitos");
    });

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

    // Validación de contraseña
    function validaContrasena(campo, mensajeError) {
        if (campo.value.length > 0 && campo.value.length < 6) {
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

    // Validación de confirmar contraseña
    function validaConfirmarContrasena(campo, mensajeError) {
        const contrasena = document.getElementById('editarContrasena').value;
        if (contrasena && campo.value !== contrasena) {
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

    // Validación de número
    function validaNumero(campo, mensajeError) {
        const regex = /^\d+$/;
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

    // Validación de código postal
    function validaCodigoPostal(campo, mensajeError) {
        const regex = /^\d{5}$/;
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

    // Validación del formulario de edición
    function validarFormularioEdicion() {
        let valido = true;
        if (!validaLargo(document.getElementById('editarNombre'), 3, "El nombre debe tener al menos 3 caracteres")) valido = false;
        if (!validaLargo(document.getElementById('editarApPaterno'), 3, "El apellido paterno debe tener al menos 3 caracteres")) valido = false;
        if (!validaLargo(document.getElementById('editarApMaterno'), 3, "El apellido materno debe tener al menos 3 caracteres")) valido = false;
        if (!validaCorreo(document.getElementById('editarCorreo'), "Ingrese un correo válido")) valido = false;
        if (!validaTelefono(document.getElementById('editarTelefono'), "Ingrese un teléfono válido (10 dígitos)")) valido = false;
        if (document.getElementById('editarContrasena').value) {
            if (!validaContrasena(document.getElementById('editarContrasena'), "La contraseña debe tener al menos 6 caracteres")) valido = false;
            if (!validaConfirmarContrasena(document.getElementById('confirmarContrasena'), "Las contraseñas no coinciden")) valido = false;
        }
        return valido;
    }

    // Validación del formulario de dirección
    function validarFormularioDireccion() {
        let valido = true;
        if (!validaLargo(document.getElementById('editarCalle'), 3, "La calle debe tener al menos 3 caracteres")) valido = false;
        if (!validaNumero(document.getElementById('editarNumero'), "Ingrese un número válido")) valido = false;
        if (!validaLargo(document.getElementById('editarColonia'), 3, "La colonia debe tener al menos 3 caracteres")) valido = false;
        if (!validaLargo(document.getElementById('editarCiudad'), 3, "La ciudad debe tener al menos 3 caracteres")) valido = false;
        if (!validaLargo(document.getElementById('editarEstado'), 3, "El estado debe tener al menos 3 caracteres")) valido = false;
        if (!validaCodigoPostal(document.getElementById('editarCodigoPostal'), "El código postal debe tener 5 dígitos")) valido = false;
        return valido;
    }
});