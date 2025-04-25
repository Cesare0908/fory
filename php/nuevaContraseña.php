<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fory - Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="../css/login.css?v=6" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <section class="body">
        <div class="container">
            <div class="login-box">
                <div class="row">
                    <div class="col-12">
                        <div class="logo">
                            <span class="logo-font">Fory</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <br>
                        <h3 class="header-title">CREAR NUEVA CONTRASEÑA</h3>
                        <?php
                        require_once 'config.php';
                        $token = isset($_GET['token']) ? ($_GET['token'] ) : '';
                        $conexion = dbConectar();
                        $stmt = $conexion->prepare("SELECT correo FROM password_resets WHERE token = ? AND expira_en > NOW()");
                        $stmt->bind_param("s", $token);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($correo);
                            $stmt->fetch();
                        ?>
                        <form class="login-form" id="nueva-contrasena-form" name="nueva-contrasena-form" onsubmit="return validarFormularioNuevaContrasena()">
                            <!-- Contraseña -->
                            <div class="form-group">
                                <label for="contrasenaN">Nueva Contraseña:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasenaN" name="contrasenaN" placeholder="Ingrese su nueva contraseña" required onblur="validaRango(this, 6, 12)">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="toggle-password-n" style="cursor: pointer;" onclick="togglePassword('contrasenaN', 'eye-icon-n')">
                                            <i class="fas fa-eye" id="eye-icon-n" style="font-size: 1.5rem;"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                                </div>
                            </div>
                            <!-- Confirmar Contraseña -->
                            <div class="form-group">
                                <label for="confirmarContrasenaN">Confirmar Contraseña:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmarContrasenaN" name="confirmarContrasenaN" placeholder="Confirme su nueva contraseña" required onblur="validaConfirmacionClave(document.getElementById('contrasenaN'), this)">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="toggle-confirm-password" style="cursor: pointer;" onclick="togglePassword('confirmarContrasenaN', 'eye-icon-confirm')">
                                            <i class="fas fa-eye" id="eye-icon-confirm" style="font-size: 1.5rem;"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                                </div>
                            </div>
                            <!-- Token oculto -->
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <!-- Botón Guardar -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block" id="btnGuardar">Guardar Contraseña</button>
                            </div>
                            <!-- Enlace de regreso -->
                            <div class="form-footer">
                                <a href="login.php">Volver al inicio de sesión</a>
                            </div>
                        </form>
                        <?php
                        } else {
                            echo '<div class="alert alert-danger text-center" role="alert">El enlace es inválido o ha expirado. <a href="RecuperarContraseña.php">Solicita un nuevo enlace</a>.</div>';
                        }
                        $stmt->close();
                        $conexion->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="footer-content">
            <p>Desarrollado por <strong>Fory Team</strong></p>
            <p>Localidad: <strong>San Nicolas Zecalacoayan, Chiautzingo, Puebla</strong></p>
            <p>Contacto: <strong>contacto@fory.com</strong></p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.js"></script>
    <script src="../js/funcionesLogin.js?v=8"></script>
</body>
</html>


