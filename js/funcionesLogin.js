document.addEventListener("DOMContentLoaded", function () {
    // FORMULARIO DE LOGIN
    const loginForm = document.querySelector("#login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            const correo = document.querySelector("#correoL");
            const contraseña = document.querySelector("#contraseñaL");
            const correoValido = validaCorreo(correo);
            const contraseñaValida = validaRango(contraseña, 6, 12);
            if (correoValido && contraseñaValida) {
                try {
                    const response = await fetch('../controladores/controlador.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `ope=LOGIN&email=${encodeURIComponent(correo.value)}&password=${encodeURIComponent(contraseña.value)}`
                    });
                    const data = await response.json();
                    if (data.tipo === 1) {
                        Swal.fire({
                            icon: "success",
                            title: "INICIO DE SESIÓN EXITOSO",
                            text: data.mensaje,
                        }).then(() => {
                            location.href = data.redireccion;
                        });
                        correo.value = '';
                        contraseña.value = '';
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: data.mensaje,
                            icon: "error",
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: "Error",
                        text: "No se pudo conectar con el servidor. Verifica tu conexión.",
                        icon: "error",
                    });
                }
            }
        });
    }

    // RECUPERAR CONTRASEÑA
    const recuperarForm = document.querySelector("#recuperar-form");
    if (recuperarForm) {
        recuperarForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            const correo = document.querySelector("#correoR");
            if (validaCorreo(correo) && validaLargo(correo, 1)) {
                try {
                    const response = await fetch('../controladores/controlador.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `ope=RecContra&email=${encodeURIComponent(correo.value)}`
                    });
                    const data = await response.json();
                    if (data.success === 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Correo Enviado',
                            text: data.mensaje,
                        }).then(() => {
                            correo.value = '';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.mensaje,
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo conectar con el servidor. Verifica tu conexión.',
                    });
                }
            }
        });
    }

    // REGISTRO DE USUARIO (ESTO ESTABA FUERA, YA ESTÁ CORREGIDO AQUÍ)
    const formularioRegistro = document.querySelector("#formularioRegistro");
    const registrarBtn = document.querySelector("#registrarBtn");
    if (registrarBtn) {
        registrarBtn.addEventListener("click", async function (event) {
            event.preventDefault();
            if (validarFormularioRegistro()) {
                const formData = new FormData(formularioRegistro);
                formData.append("ope", "guardarUS");
                try {
                    const response = await fetch('../controladores/controlador.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success === 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Usuario registrado con éxito!',
                            text: data.mensaje,
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.error === 1 ? 'Correo ya registrado' : 'Usuario no registrado',
                            text: data.mensaje,
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor.',
                    });
                }
            }
        });
    }
    
// NUEVA CONTRASEÑA
const nuevaContrasenaForm = document.querySelector("#nueva-contrasena-form");
if (nuevaContrasenaForm) {
    nuevaContrasenaForm.addEventListener("submit", async function (event) {
        event.preventDefault();
        const contrasena = document.querySelector("#contrasenaN");
        const confirmarContrasena = document.querySelector("#confirmarContrasenaN");
        const token = document.querySelector("input[name='token']").value;
        const contrasenaValida = validaRango(contrasena, 6, 12);
        const confirmacionValida = validaConfirmacionClave(contrasena, confirmarContrasena);
        if (contrasenaValida && confirmacionValida) {
            try {
                const response = await fetch('../controladores/controlador.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ope=NuevaContrasena&token=${encodeURIComponent(token)}&contrasenaN=${encodeURIComponent(contrasena.value)}&confirmarContrasenaN=${encodeURIComponent(confirmarContrasena.value)}`
                });
                const data = await response.json();
                if (data.success === 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Contraseña Actualizada',
                        text: data.mensaje,
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje,
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo conectar con el servidor. Verifica tu conexión.',
                });
            }
        }
    });
}  
});

function togglePassword() {
    const contraseñaInput = document.getElementById("contraseñaL");
    const icon = document.getElementById("eye-icon");
    if (contraseñaInput.type === "password") {
        contraseñaInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        contraseñaInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

function validaLargo(campo, largo) {
    if (campo.value.length < largo) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        return true;
    }
}

function validaCorreo(campo) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (regex.test(campo.value) && campo.value.length >= 5) {
        campo.classList.add("is-valid");
        campo.classList.remove("is-invalid");
        return true;
    } else {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        return false;
    }
}

function validaRango(campo, min, max) {
    if (campo.value.length < min || campo.value.length > max) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        return true;
    }
}

function validaConfirmacionClave(campo1, campo2) {
    if (campo1.value !== campo2.value) {
        campo2.classList.add("is-invalid");
        campo2.classList.remove("is-valid");
        return false;
    } else {
        campo2.classList.remove("is-invalid");
        campo2.classList.add("is-valid");
        return true;
    }
}

function validaContraseña(campo) {
    if (campo.value.length < 6) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        return true;
    }
}

function validarFormularioRegistro() {
    let valido = true;
    if (!validaLargo(document.getElementById('nombre'), 1)) valido = false;
    if (!validaLargo(document.getElementById('ap_paterno'), 1)) valido = false;
    if (!validaLargo(document.getElementById('ap_materno'), 1)) valido = false;
    if (!validaLargo(document.getElementById('correo'), 1)) valido = false;
    if (!validaCorreo(document.getElementById('correo'))) valido = false;
    if (!validaLargo(document.getElementById('telefono'), 10)) valido = false;
    if (!validaLargo(document.getElementById('contraseña'), 1)) valido = false;
    if (!validaRango(document.getElementById('contraseña'), 6, 12)) valido = false;
    if (!validaConfirmacionClave(document.getElementById('contraseña'), document.getElementById('confirmar_contraseña'))) valido = false;
    return valido;
}

function validarFormularioLogin() {
    let valido = true;
    const correo = document.getElementById('correoL');
    const contraseña = document.getElementById('contraseñaL');
    if (!validaLargo(correo, 1) || !validaCorreo(correo)) valido = false;
    if (!validaLargo(contraseña, 1) || !validaRango(contraseña, 6, 12)) valido = false;
    return valido;
}