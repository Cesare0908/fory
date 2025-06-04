<?php
// Tu clave API de OpenRouter
$apiKey = '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';

// Datos de la solicitud
$data = [
    "model" => "openai/gpt-3.5-turbo", // puedes cambiar a otros modelos compatibles
    "messages" => [
        ["role" => "user", "content" => "PUEDES DARME UNA RECETA BARAT CON HUEVOS?"]
    ]
];

// Inicializar cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// Encabezados HTTP
$headers = [
    "Authorization: Bearer $apiKey",
    "HTTP-Referer: https://tupagina.com",         // cambia esto por tu dominio real
    "X-Title: Prueba Julio con OpenRouter",       // título para identificar la app
    "Content-Type: application/json"
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Ejecutar solicitud
$response = curl_exec($ch);

// Verificar errores
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Decodificar y mostrar la respuesta
    $decoded = json_decode($response, true);
    echo "<pre>";
    print_r($decoded['choices'][0]['message']['content']);
    echo "</pre>";
}

curl_close($ch);
?>
