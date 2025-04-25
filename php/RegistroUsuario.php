<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foryfay - Registro</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link href="../css/RegistroUsuario.css?v=233" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>

<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.js"></script>


</head>
<body>
<div class="container mt-3">
    <div class="text-center mb-5">
        <h1 class="display-3">FORYFAY</h1>
    </div>

    <div class="login-box">
        <div class="text-center mb-5">
            <h2 class="display-3">CREAR NUEVA CUENTA</h2>
        </div>
        <form action="#" method="POST" class="formularioRegistro" id="formularioRegistro" name="formularioRegistro" onsubmit="return validarFormulario()">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required onblur="validaLargo(this, 1)">
                    <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
                    <div class="valid-feedback">Correcto!</div>

                </div>
                <div class="col-md-4">
                    <label for="ap_paterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="ap_paterno" name="ap_paterno" required onblur="validaLargo(this, 1)">
                    <div class="invalid-feedback">Por favor ingresa tu apellido paterno.</div>
                    <div class="valid-feedback">Correcto!</div>

                </div>
                <div class="col-md-4">
                    <label for="ap_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="ap_materno" name="ap_materno" required onblur="validaLargo(this, 1)">
                    <div class="invalid-feedback">Por favor ingresa tu apellido materno.</div>
                    <div class="valid-feedback">Correcto!</div>

                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="correo" class="form-label">Correo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="correo" name="correo" required onblur="validaCorreo(this)">
                

                    <div class="invalid-feedback">El correo no es válido o tiene una longitud incorrecta.</div>
                    <div class="valid-feedback">Correcto!</div>

                    <div class="valid-feedback">¡Correo válido!</div>
                </div>
            </div>

            <div class="col-md-12">
                <label for="telefono" class="form-label">Teléfono</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="tel" class="form-control" id="telefono" name="telefono" pattern="[0-9]{10}" required onblur="validaLargo(this, 10)">
                <div class="invalid-feedback">Ingrese un telefoo calido. El teléfono debe tener 10 dígitos.</div>
                <div class="valid-feedback">¡Numero valido!</div>

            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="contraseña" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="contraseña" name="contraseña" required onblur="validaRango(this, 6, 12)">

                    <div class="invalid-feedback">La contraseña debe tener entre 6 y 12 caracteres.</div>
                    <div class="valid-feedback">¡Contraseña válida!</div>
                    </div>

                </div>
                <div class="col-md-6">
                    <label for="confirmar_contraseña" class="form-label">Confirmar Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña" required onblur="validaConfirmacionClave(contraseña, this)">
                    </div>
                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                    <div class="valid-feedback">¡Contraseñas coincidentes!</div>
                </div>
            </div>

            <div class="text-center mb-4">
            <button type="submit" class="btn btn-warning w-50 mx-auto" id="registrarBtn" name="registrarBtn">Registrar</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <p><a href="login.php" class="text-muted">¿Ya tienes cuenta? Inicia sesión</a></p>
        </div>
    </div>
</div>

<script src="../js/funcionesLogin.js?v=3"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
