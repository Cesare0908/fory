document.addEventListener('DOMContentLoaded', () => {
    const chatInput = document.getElementById('chatInput');
    const presupuestoInput = document.getElementById('presupuestoInput');
    const chatContainer = document.getElementById('chatContainer');
    const chatbotModal = document.getElementById('chatbotModal');

    // Añadir mensaje al contenedor del chat
    function agregarMensaje(mensaje, tipo) {
        const div = document.createElement('div');
        div.className = tipo === 'user' ? 'user-msg' : 'bot-msg';
        div.textContent = mensaje;
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Enviar mensaje al servidor
    window.enviarMensaje = function() {
        const mensaje = chatInput.value.trim();
        const presupuesto = presupuestoInput.value.trim();
        const id_usuario = null; 
        if (!mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Mensaje vacío',
                text: 'Por favor, escribe un mensaje.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        // Mostrar mensaje del usuario
        agregarMensaje(mensaje, 'user');

        // Preparar datos para enviar
        const data = {
            mensaje: mensaje,
            id_usuario: id_usuario,
            presupuesto: presupuesto ? parseFloat(presupuesto) : null
        };

        // Enviar solicitud al servidor
        fetch('http://localhost/fory-final/controladores/ControladorCliente/controladorChat.php?v=153555', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.respuesta) {
                agregarMensaje(data.respuesta, 'bot');
            } else {
                agregarMensaje('Lo siento, ocurrió un error: ' + (data.error || 'Respuesta inválida'), 'bot');
            }
            chatInput.value = '';
            presupuestoInput.value = '';
        })
        .catch(error => {
            console.error('Error en la solicitud:', error);
            agregarMensaje('Error de conexión. Por favor, intenta de nuevo.', 'bot');
            chatInput.value = '';
            presupuestoInput.value = '';
        });
    };

    // Limpiar chat al cerrar el modal
    chatbotModal.addEventListener('hidden.bs.modal', () => {
        chatContainer.innerHTML = '';
    });

    // Enviar mensaje con tecla Enter
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            enviarMensaje();
        }
    });
}); 