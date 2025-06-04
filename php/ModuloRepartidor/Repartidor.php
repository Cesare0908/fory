<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fory - Dashboard Repartidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="http://localhost/fory-final/css/DISENOSREP/repartidor.css?v=3" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="logo-font">Fory</span>
            <button class="btn-close text-white" onclick="toggleSidebar()"></button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" onclick="mostrarSeccion('pedidos')"><i class="fas fa-box"></i> Pedidos Pendientes</a></li>
            <li><a href="#" onclick="mostrarSeccion('historial')"><i class="fas fa-history"></i> Historial de Entregas</a></li>
            <li><a href="#" onclick="mostrarSeccion('perfil')"><i class="fas fa-user"></i> Perfil</a></li>
            <li><a href="#" onclick="mostrarSeccion('vehiculo')"><i class="fas fa-car"></i> Vehículo</a></li>
            <li><a href="#" onclick="mostrarSeccion('ubicacion')"><i class="fas fa-map-marker-alt"></i> Actualizar Ubicación</a></li>
            <li><a href="#" onclick="mostrarSeccion('notificaciones')"><i class="fas fa-bell"></i> Notificaciones</a></li>
            <li><a href="#" onclick="mostrarSeccion('incidencia')"><i class="fas fa-exclamation-circle"></i> Reportar Incidencia</a></li>
            <li><a href="http://localhost/fory-final/php/salir.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="main-content">
        <header class="header">
            <button class="btn btn-hamburger" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h3 class="header-title">Panel de Repartidor</h3>
            <div class="disponibilidad">
                <label for="disponibilidad">Disponibilidad:</label>
                <select id="disponibilidad" class="form-control" onchange="actualizarDisponibilidad()">
                    <option value="disponible">Disponible</option>
                    <option value="no disponible">No Disponible</option>
                </select>
            </div>
        </header>
        <section class="content">
            <!-- Sección Pedidos Pendientes -->
            <div id="pedidos-section" class="section active">
                <h4>Pedidos Pendientes</h4>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filtro-estado" class="form-control" onchange="cargarPedidos()">
                            <option value="todos">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregado">Entregado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div id="pedidos-lista" class="row"></div>
            </div>
            <!-- Sección Historial de Entregas -->
            <div id="historial-section" class="section">
                <h4>Historial de Entregas</h4>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="fecha-inicio">Fecha Inicio:</label>
                        <input type="date" id="fecha-inicio" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha-fin">Fecha Fin:</label>
                        <input type="date" id="fecha-fin" class="form-control">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary" onclick="cargarHistorial()">Filtrar</button>
                    </div>
                </div>
                <div id="historial-lista" class="row"></div>
            </div>
            <!-- Sección Perfil -->
            <div id="perfil-section" class="section">
                <h4>Perfil</h4>
                <div class="card">
                    <div class="card-body">
                        <div id="perfil-vista">
                            <p><strong>Nombre:</strong> <span id="perfil-nombre"></span></p>
                            <p><strong>Apellido Paterno:</strong> <span id="perfil-ap_paterno"></span></p>
                            <p><strong>Apellido Materno:</strong> <span id="perfil-ap_materno"></span></p>
                            <p><strong>Teléfono:</strong> <span id="perfil-telefono"></span></p>
                            <p><strong>Correo:</strong> <span id="perfil-correo"></span></p>
                            <button class="btn btn-primary" onclick="mostrarFormularioPerfil()">Editar</button>
                        </div>
                        <form id="formularioPerfil" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" class="form-control" id="nombre" required>
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="ap_paterno">Apellido Paterno:</label>
                                    <input type="text" class="form-control" id="ap_paterno" required>
                                    <div class="invalid-feedback">El apellido paterno es obligatorio.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="ap_materno">Apellido Materno:</label>
                                    <input type="text" class="form-control" id="ap_materno" required>
                                    <div class="invalid-feedback">El apellido materno es obligatorio.</div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="telefono">Teléfono:</label>
                                    <input type="text" class="form-control" id="telefono" required>
                                    <div class="invalid-feedback">El teléfono debe tener 10 dígitos.</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="correo">Correo:</label>
                                <input type="email" class="form-control" id="correo" required>
                                <div class="invalid-feedback">Ingrese un correo válido.</div>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="guardarPerfil()">Guardar</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelarEdicionPerfil()">Cancelar</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Sección Vehículo -->
            <div id="vehiculo-section" class="section">
                <h4>Vehículo</h4>
                <div class="card">
                    <div class="card-body">
                        <div id="vehiculo-vista">
                            <p><strong>Tipo de Vehículo:</strong> <span id="vehiculo-tipo"></span></p>
                            <p><strong>Marca:</strong> <span id="vehiculo-marca"></span></p>
                            <p><strong>Modelo:</strong> <span id="vehiculo-modelo"></span></p>
                            <p><strong>Color:</strong> <span id="vehiculo-color"></span></p>
                            <p><strong>Placas:</strong> <span id="vehiculo-placas"></span></p>
                            <button class="btn btn-primary" onclick="mostrarFormularioVehiculo()">Editar</button>
                        </div>
                        <form id="formularioVehiculo" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="tipo_vehiculo">Tipo de Vehículo:</label>
                                    <select id="tipo_vehiculo" class="form-control" required>
                                        <option value="moto">Moto</option>
                                        <option value="bicicleta">Bicicleta</option>
                                        <option value="camioneta">Camioneta</option>
                                        <option value="auto">Auto</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="marca">Marca:</label>
                                    <input type="text" class="form-control" id="marca" required>
                                    <div class="invalid-feedback">La marca es obligatoria.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="modelo">Modelo:</label>
                                    <input type="text" class="form-control" id="modelo" required>
                                    <div class="invalid-feedback">El modelo es obligatorio.</div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="color">Color:</label>
                                    <input type="text" class="form-control" id="color" required>
                                    <div class="invalid-feedback">El color es obligatorio.</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="placas">Placas:</label>
                                <input type="text" class="form-control" id="placas">
                            </div>
                            <button type="button" class="btn btn-primary" onclick="guardarVehiculo()">Guardar</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelarEdicionVehiculo()">Cancelar</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Sección Actualizar Ubicación -->
            <div id="ubicacion-section" class="section">
                <h4>Actualizar Ubicación</h4>
                <div class="card">
                    <div class="card-body">
                        <form id="formularioUbicacion">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="latitud">Latitud:</label>
                                    <input type="number" step="any" class="form-control" id="latitud" required>
                                    <div class="invalid-feedback">La latitud es obligatoria.</div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="longitud">Longitud:</label>
                                    <input type="number" step="any" class="form-control" id="longitud" required>
                                    <div class="invalid-feedback">La longitud es obligatoria.</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="actualizarUbicacion()">Actualizar</button>
                        </form>
                        <div id="map-ubicacion" style="height: 300px; margin-top: 20px;"></div>
                    </div>
                </div>
            </div>
            <!-- Sección Notificaciones -->
            <div id="notificaciones-section" class="section">
                <h4>Notificaciones</h4>
                <div id="notificaciones-lista" class="card">
                    <div class="card-body"></div>
                </div>
            </div>
            <!-- Sección Reportar Incidencia -->
            <div id="incidencia-section" class="section">
                <h4>Reportar Incidencia</h4>
                <div class="card">
                    <div class="card-body">
                        <form id="formularioIncidencia">
                            <div class="form-group">
                                <label for="id_pedido_incidencia">ID del Pedido:</label>
                                <input type="number" class="form-control" id="id_pedido_incidencia" required>
                                <div class="invalid-feedback">El ID del pedido es obligatorio.</div>
                            </div>
                            <div class="form-group">
                                <label for="mensaje_incidencia">Descripción del Problema:</label>
                                <textarea class="form-control" id="mensaje_incidencia" rows="5" required></textarea>
                                <div class="invalid-feedback">La descripción es obligatoria.</div>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="reportarIncidencia()">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- Modal Detalles del Pedido -->
        <div class="modal fade" id="detallesPedidoModal" tabindex="-1" aria-labelledby="detallesPedidoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detallesPedidoModalLabel">Detalles del Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detalles-pedido-content"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal para Cancelar Pedido -->
        <div class="modal fade" id="cancelarPedidoModal" tabindex="-1" aria-labelledby="cancelarPedidoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelarPedidoModalLabel">Cancelar Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formularioCancelar">
                            <div class="form-group">
                                <label for="motivo_cancelacion">Motivo de la Cancelación:</label>
                                <textarea class="form-control" id="motivo_cancelacion" rows="4" required></textarea>
                                <div class="invalid-feedback">El motivo es obligatorio.</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-danger" onclick="confirmarCancelacion()">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-content">
            <p>Desarrollado por <strong>Julio Cesar Ruiz Perez & Carlos Alejandro Martinez</strong></p>
            <p>Localidad: <strong>San Nicolas Zecalacoayan, Chiautzingo, Puebla</strong></p>
            <p>Contacto: <strong>2481955951</strong></p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
    <script src="http://localhost/fory-final/JS/FuncionesRep/funcionesRep.js?ver=6"></script>
</body>
</html>