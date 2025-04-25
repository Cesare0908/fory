<?php
header('Content-Type: application/json');
// Simula una verificación de notificaciones (reemplaza con tu lógica real)
$count = rand(0, 5); // Ejemplo: número aleatorio de notificaciones
echo json_encode(['count' => $count]);
?>