<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - FORY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../css/DISEÑOSCLIENTE/pedidos.css?v=8">
    <style>
        .btn-cancelar {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 10px;
        }
        .btn-cancelar:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }
        .btn-cancelar:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="text-white text-decoration-none">FORY</a></h4>
            <button class="btn-close btn-close-white" id="cerrar-sidebar" aria-label="Cerrar menú"></button>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/fory-final/php/ModuloCliente/EditarPerfil.php"><i class="fas fa-user me-2"></i> Administrar Perfil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="http://localhost/fory-final/php/ModuloCliente/historial_compras.php"><i class="fas fa-history me-2"></i> Historial de Compras</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/fory-final/php/salir.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- Encabezado -->
    <header class="bg-dark text-white py-3 fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-light me-2" id="abrir-sidebar"><i class="fas fa-bars"></i></button>
                <span class="h4 mb-0"><a href="http://localhost/fory-final/php/ModuloCliente/cliente.php" class="text-white text-decoration-none">FORY</a></span>
            </div>
            <div class="d-flex align-items-center">
                <div class="dropdown me-3">
                    <a href="#" class="text-white user-icon" id="user-menu" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="http://localhost/fory-final/php/ModuloCliente/EditarPerfil.php">Ver Perfil</a></li>
                        <li><a class="dropdown-item" href="http://localhost/fory-final/php/salir.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-5 pt-5">
        <h2 class="mb-4">Historial de Compras</h2>
        <div id="historial-compras">
            <!-- Pedidos cargados dinámicamente -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="http://localhost/fory-final/js/FuncionesCliente/historial_compras.js?v=85554"></script>
</body>
</html>