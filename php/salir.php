<?php
require_once 'config.php';

$usu = new Usuarios();
$usu->Salir();

header("Location: " . RUTA . "/php/login.php");
exit;
?>