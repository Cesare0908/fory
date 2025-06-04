<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot de Entregas</title>
    <!-- Bootstrap para el modal (puedes usar otro framework si prefieres) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');

        body {
            background-image: url('https://i.ibb.co/Kpn1Nvqd/image-Photoroom.jpg');
            font-family: 'Roboto', sans-serif;
        } 

        .chatbot-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0057B7;
            color: #FFFFFF;
            border: none;
            border-radius: 50px;
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chatbot-button:hover {
            background: #003087;
        }

        .modal-content {
            background: rgba(179, 216, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #181818;
            font-weight: 700;
        }

        .chat-container {
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
        }

        .chat-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
        }

        .user-message {
            background: #0057B7;
            color: #FFFFFF;
            margin-left: auto;
        }

        .bot-message {
            background: #FFFFFF;
            color: #181818;
            margin-right: auto;
        }

        .chat-input {
            display: flex;
            gap: 10px;
        }

        .chat-input input {
            border-radius: 10px;
            border: 1px solid #CED4DA;
            padding: 10px;
            flex-grow: 1;
        }

        .chat-input input:focus {
            border-color: rgb(136, 176, 219);
            box-shadow: 0 0 8px rgba(89, 157, 230, 0.3);
            outline: none;
        }

        .chat-input button {
            background: #0057B7;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 700;
        }

        .chat-input button:hover {
            background: #003087;
        }
    </style>
</head>
<body>
    <!-- Botón para abrir el chatbot -->
    <button class="chatbot-button" data-bs-toggle="modal" data-bs-target="#chatbotModal">Chatbot</button>

    <!-- Modal del chatbot -->
    <div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatbotModalLabel">Chatbot de Entregas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-container" id="chatContainer">
                        <!-- Los mensajes se añadirán aquí dinámicamente -->
                    </div>
                    <div class="chat-input">
                        <input type="text" id="chatInput" placeholder="Escribe tu mensaje..." autocomplete="off">
                        <input type="number" id="presupuestoInput" placeholder="Presupuesto (opcional)" min="0" step="0.01">
                        <button onclick="enviarMensaje()">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ID del usuario (puedes obtenerlo desde tu sistema de autenticación)
        const idUsuario = 2; // Ejemplo, cámbialo según el usuario autenticado

        function enviarMensaje() {
            const input = document.getElementById('chatInput');
            const presupuestoInput = document.getElementById('presupuestoInput');
            const mensaje = input.value.trim();
            const presupuesto = presupuestoInput.value.trim();

            if (!mensaje) return;

            // Añadir mensaje del usuario al chat
            const chatContainer = document.getElementById('chatContainer');
            const userMessage = document.createElement('div');
            userMessage.className = 'chat-message user-message';
            userMessage.textContent = mensaje;
            chatContainer.appendChild(userMessage);

            // Enviar solicitud AJAX
            const data = {
                mensaje: mensaje,
                id_usuario: idUsuario,
                presupuesto: presupuesto ? parseFloat(presupuesto) : null
            };

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                const botMessage = document.createElement('div');
                botMessage.className = 'chat-message bot-message';
                botMessage.textContent = data.respuesta || data.error || 'Error en la respuesta';
                chatContainer.appendChild(botMessage);
                chatContainer.scrollTop = chatContainer.scrollHeight; // Desplazar al final
            })
            .catch(error => {
                console.error('Error:', error);
                const botMessage = document.createElement('div');
                botMessage.className = 'chat-message bot-message';
                botMessage.textContent = 'Error al conectar con el servidor';
                chatContainer.appendChild(botMessage);
            });

            // Limpiar inputs
            input.value = '';
            presupuestoInput.value = '';
        }

        // Enviar mensaje con la tecla Enter
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') enviarMensaje();
        });
    </script>
</body>
</html>