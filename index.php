<?php
session_start();

if (!defined("RUTA2")) {
    define("RUTA2", "http://localhost/fory-final");
}

if (isset($_SESSION["sistema"]) && $_SESSION["sistema"] === "foryfay" && isset($_SESSION["rol"])) {
    $id_rol = $_SESSION["rol"];

    if (isset($_GET["pag"])) {
        $pag = $_GET["pag"];

        if ($id_rol == 1 && $pag == "1") {
            include_once("http://localhost/php/ModuloAdmin.php");
        } elseif ($id_rol == 2 && $pag == "2") {
            include_once("php/ModuloRepartidor/Repartidor.php");
        } elseif ($id_rol == 3 && $pag == "3") {
            include_once("empleado.php");
        } elseif ($id_rol == 4 && $pag == "4") {
            include_once("php/ModuloCliente/cliente.php");
        } else {
            // Página o rol no válidos
            header("Location: " . RUTA . "/salir.php");
            exit;
        }
    } else {
        // Redirigir por defecto según rol
        header("Location: " . RUTA2 . "/?pag=" . $id_rol);
        exit;
    }
} else {
    // No hay sesión válida, redirigir a login
    header("Location: " . RUTA2 . "/php/login.php");
    exit;
}
