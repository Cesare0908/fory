<?php
// chatbot_api.php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = '127.0.0.1';
$dbname = 'fory';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Clave API de OpenRouter
$apiKey = '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';

// Función para obtener productos de la base de datos
function getProductos($pdo) {
    $stmt = $pdo->query("SELECT id_producto, nombre_producto, descripcion, precio, stock, id_categoria FROM producto WHERE disponibilidad = 'disponible'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener historial de compras de un usuario
function getHistorialCompras($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT p.nombre_producto, p.id_categoria, c.nombre_categoria 
                           FROM historial_compras hc 
                           JOIN producto p ON hc.id_producto = p.id_producto 
                           JOIN categoria c ON hc.id_categoria = c.id_categoria 
                           WHERE hc.id_usuario = ?");
    $stmt->execute([$id_usuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Procesar la solicitud del chatbot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $mensaje = $input['mensaje'] ?? '';
    $id_usuario = $input['id_usuario'] ?? null;
    $presupuesto = $input['presupuesto'] ?? null;

    if (empty($mensaje)) {
        echo json_encode(['error' => 'Mensaje vacío']);
        exit;
    }

    // Obtener productos disponibles
    $productos = getProductos($pdo);
    $lista_productos = array_map(function($prod) {
        return "{$prod['nombre_producto']} ({$prod['descripcion']}, Precio: {$prod['precio']} MXN, Stock: {$prod['stock']})";
    }, $productos);
    $contexto_productos = "Productos disponibles: " . implode(", ", $lista_productos);

    // Obtener historial de compras si hay usuario
    $contexto_historial = "";
    if ($id_usuario) {
        $historial = getHistorialCompras($pdo, $id_usuario);
        if ($historial) {
            $compras = array_map(function($compra) {
                return "{$compra['nombre_producto']} (Categoría: {$compra['nombre_categoria']})";
            }, $historial);
            $contexto_historial = "Historial de compras del usuario: " . implode(", ", $compras);
        }
    }

    // Preparar el prompt según el tipo de consulta
    $prompt = "";
    if (stripos($mensaje, 'receta') !== false) {
        $prompt = "Basándote en los siguientes productos disponibles: $contexto_productos, sugiere una receta que utilice algunos de estos productos. La receta debe ser clara, incluir ingredientes y pasos. Responde en español.";
    } elseif (stripos($mensaje, 'dulce') !== false) {
        $prompt = "El usuario quiere algo dulce. Basándote en los productos: $contexto_productos, recomienda productos dulces disponibles y explica por qué los sugieres. Responde en español.";
    } elseif ($presupuesto !== null) {
        $prompt = "El usuario tiene un presupuesto de $presupuesto MXN. Basándote en los productos: $contexto_productos, sugiere una combinación de productos que pueda comprar dentro de ese presupuesto. Incluye precios y el total. Responde en español.";
    } else {
        $prompt = "Responde a la siguiente consulta del usuario: '$mensaje'. Usa este contexto de productos: $contexto_productos. Si hay historial: $contexto_historial. Responde en español.";
    }

    // Preparar datos para OpenRouter
    $data = [
        "model" => "openai/gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "Eres un asistente de un sistema de entregas a domicilio. Ayuda al usuario con recetas, recomendaciones de productos y sugerencias basadas en su presupuesto o preferencias, usando solo los productos proporcionados."],
            ["role" => "user", "content" => $prompt]
        ]
    ];

    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "HTTP-Referer: https://tupagina.com",
        "X-Title: Sistema de Entregas Fory",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Ejecutar solicitud
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Error en la API: ' . curl_error($ch)]);
        exit;
    }

    // Decodificar respuesta
    $decoded = json_decode($response, true);
    if (isset($decoded['choices'][0]['message']['content'])) {
        echo json_encode(['respuesta' => $decoded['choices'][0]['message']['content']]);
    } else {
        echo json_encode(['error' => 'Respuesta inválida de la API']);
    }

    curl_close($ch);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>