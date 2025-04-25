<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORY - Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css?v=4">
</head>

<body>
    <!-- Header - Visible en todas las pantallas -->
    <header class="header-mobile" id="header">
        <div class="d-flex justify-content-between align-items-center p-4">
            <h2 id="titulo-seccion" class="mb-0"></h2>
            <div class="d-flex align-items-center">
                <button class="btn btn-notificacion me-3" onclick="checkNotifications()">
                    <i class="fas fa-bell"></i>
                    <span id="notification-count" class="badge bg-danger"></span>
                </button>
                <button class="btn btn-dark-mode me-3" onclick="toggleDarkMode()">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="btn btn-hamburguesa me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-perfil" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="" onclick="cargarContenido('perfil')">
                            <i class="fas fa-user me-2"></i>Mi Perfil
                        </a>
                        <a class="dropdown-item" href="#" onclick="salir()">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Menú Lateral -->
            <nav class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <div class="d-flex flex-column">
                    <h1 class="titulo-principal mb-4">FORY</h1>
                    <div class="buscador-container mb-3">
                        <input type="text" class="form-control buscador" id="buscador" placeholder="Buscar módulo..." oninput="filtrarMenu()">
                    </div>
                    <button class="btn-menu" data-modulo="dashboard" onclick="cargarContenido('dashboard')"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</button>
                    <button class="btn-menu" data-modulo="pedidos" onclick="cargarContenido('pedidos')"><i class="fas fa-shopping-cart me-2"></i>Gestión Pedidos</button>
                    <button class="btn-menu" data-modulo="usuarios" onclick="cargarContenido('usuarios')"><i class="fas fa-users me-2"></i>Gestión Usuarios</button>
                    <button class="btn-menu" data-modulo="repartidores" onclick="cargarContenido('repartidores')">
                        <i class="fas fa-motorcycle me-2"></i>Repartidores
                    </button>

                    <button class="btn-menu" data-modulo="productos" onclick="cargarContenido('productos')"><i class="fas fa-box me-2"></i>Gestión Productos</button>
                    <button class="btn-menu" data-modulo="categorias" onclick="cargarContenido('categorias')"><i class="fas fa-tags me-2"></i>Gestión Categorías</button>
                    <button class="btn-menu" data-modulo="reportes" onclick="cargarContenido('reportes')"><i class="fas fa-chart-bar me-2"></i>Reportes Ventas</button>
                    <button class="btn-menu btn-salir" onclick="salir()"><i class="fas fa-sign-out-alt me-2"></i>Salir</button>
                </div>
            </nav>

            <!-- Área de Contenido -->
            <div class="col-md-9 col-lg-10 contenido">
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                <iframe id="contenido" src="dashboard.php" onload="hideLoading()"></iframe>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/funcionesAdmin.js?v=7"></script>
</body>

</html>