<?php
session_start();

if (!defined("RUTA2")) {
    define("RUTA2", "http://localhost/fory-final");
}

if (isset($_SESSION["sistema"]) && $_SESSION["sistema"] === "foryfay" && isset($_SESSION["rol"])) {
    $id_rol = filter_var($_SESSION["rol"], FILTER_SANITIZE_NUMBER_INT);

    if (isset($_GET["pag"])) {
        $pag = filter_var($_GET["pag"], FILTER_SANITIZE_NUMBER_INT);

        if ($id_rol == 1 && $pag == 1) {
            include_once(__DIR__ . "/php/ModuloAdmin/ModuloAdmin.php");
        } elseif ($id_rol == 2 && $pag == 2) {
            include_once(__DIR__ . "/php/ModuloRepartidor/Repartidor.php");
        } elseif ($id_rol == 3 && $pag == 3) {
            include_once(__DIR__ . "/empleado.php");
        } elseif ($id_rol == 4 && $pag == 4) {
            include_once(__DIR__ . "/php/ModuloCliente/cliente.php");
        } else {
            // Página o rol no válidos
            error_log("Acceso no autorizado: rol=$id_rol, pag=$pag");
            header("Location: " . RUTA2 . "/php/salir.php");
            exit;
        }
    } else {
        // Redirigir por defecto según rol
        header("Location: " . RUTA2 . "/?pag=" . $id_rol);
        exit;
    }
} else {
    // No hay sesión válida, redirigir a login
    error_log("Sesión no válida: sistema o rol no definidos");
    header("Location: " . RUTA2 . "/php/login.php");
    exit;
}