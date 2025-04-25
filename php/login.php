
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fory - Inicio de Sesión</title>
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
                    <div class="col-sm-6">
                        <div class="logo">
                            <span class="logo-font">Fory</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <br>
                        <h3 class="header-title">INICIAR SESIÓN</h3>
                        <form class="login-form" id="login-form" name="login-form" onsubmit="return validarFormularioLogin()">
                            <!-- Correo -->
                            <div class="form-group">
                                <label for="correoL">Correo:</label>
                                <input type="email" class="form-control" id="correoL" name="correoL" placeholder="Ingrese su correo" required onblur="validaCorreo(this)">
                                <div class="invalid-feedback">Por favor ingrese un correo válido.</div>
                            </div>
                            <!-- Contraseña -->
                            <div class="form-group">
                                <label for="contraseñaL">Contraseña:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contraseñaL" name="contraseñaL" placeholder="Ingrese su contraseña" required onblur="validaRango(this, 6, 12)">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="toggle-password" style="cursor: pointer;" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="eye-icon" style="font-size: 1.5rem;"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                                </div>
                            </div>
                            <!-- Botón Iniciar Sesión -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block" id="btnIngresar" name="btnIngresar">Ingresar</button>
                            </div>
                            <!-- Enlaces de registro y olvido -->
                            <div class="form-footer">
                                <a href="RecuperarContraseña.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                                <div>¿No tienes cuenta? <a href="RegistroUsuario.php">Regístrate</a></div>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-6 hide-on-mobile">
                        <div id="demo" class="carousel slide" data-bs-ride="carousel" aria-label="Carrusel de promociones">
                            <!-- Indicadores -->
                            <ul class="carousel-indicators">
                                <li data-bs-target="#demo" data-bs-slide-to="0" class="active"></li>
                                <li data-bs-target="#demo" data-bs-slide-to="1"></li>
                            </ul>
                            <!-- El Slideshow -->
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <div class="slider-feature-card">
                                        <img src="../Recursos/imagen/1.png" alt="Imagen 1" onerror="this.src='../Recursos/imagen/fallback.jpg'">
                                        <h3 class="slider-title">ENTREGAS A DOMICILIO</h3>
                                        <p class="slider-description">Hasta la puerta de tu hogar o recoge en tienda</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <div class="slider-feature-card">
                                        <img src="../Recursos/imagen/2.png" alt="Imagen 2" onerror="this.src='../Recursos/imagen/fallback.jpg'">
                                        <h3 class="slider-title">Disfruta de tus productos sin moverte del sofá.</h3>
                                        <p class="slider-description">Tu pedido listo para llevar o recoger.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Controles -->
                            <a class="carousel-control-prev" href="#demo" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next" href="#demo" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
    <div class="footer-content">
        <p>Desarrollado por <strong>Julio Cesar Ruiz Perez && Carlos Alejandro Martinez</strong></p>
        <p>Localidad: <strong>San Nicolas Zecalacoayan, Chiautzingo, Puebla</strong></p>
        <p>Contacto: <strong>2481955951</strong></p>
    </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.js"></script>
    <script src="../js/funcionesLogin.js?v=4"></script>
</body>
</html>
