<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fory - Recuperar Contraseña</title>
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
                        <h3 class="header-title">RECUPERAR CONTRASEÑA</h3>
                        <form class="login-form" id="recuperar-form" name="recuperar-form" onsubmit="return validarFormularioRecuperar()">
                            <!-- Correo -->
                            <div class="form-group">
                                <label for="correoR">Correo:</label>
                                <input type="email" class="form-control" id="correoR" name="correoR" placeholder="Ingrese su correo" required onblur="validaCorreo(this)">
                                <div class="invalid-feedback">Por favor ingrese un correo válido.</div>
                            </div>
                            <!-- Botón Enviar -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block" id="btnRecuperar" name="btnRecuperar">Enviar Enlace</button>
                            </div>
                            <!-- Enlace de regreso -->
                            <div class="form-footer">
                                <a href="login.php">Volver al inicio de sesión</a>
                            </div>
                        </form>
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
    <script src="../js/funcionesLogin.js?v=5"></script>
</body>
</html>