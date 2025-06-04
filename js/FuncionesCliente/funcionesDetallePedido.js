const id_pedido = new URLSearchParams(window.location.search).get('id_pedido');
// Logging
console.log(`[${new Date().toISOString()}] Enviando id_pedido: ${id_pedido}`);

fetch("http://localhost/fory-final/controladores/ControladorCliente/controladorDetallePedido.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `operacion=detallePedido&id_pedido=${id_pedido}`
})
.then(respuesta => respuesta.json())
.then(datos => {
    const contenido = document.getElementById("contenido-detalles-pedido");
    if (datos.exito) {
        const pedido = datos.pedido;
        const elementos = datos.elementos;
        let html = `
            <p><strong>Fecha:</strong> ${pedido.fecha_pedido}</p>
            <p><strong>Estado:</strong> ${pedido.estado}</p>
            <p><strong>Total:</strong> $${pedido.total}</p>
            <p><strong>Costo de Envío:</strong> $${pedido.costo_envio}</p>
            <p><strong>Método de Pago:</strong> ${pedido.nombre_metodo}</p>
            <p><strong>Dirección:</strong> ${pedido.direccion}</p>
            <h4>Productos</h4>
            <table class="table tabla-detalles-pedido">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
        `;
        elementos.forEach(elemento => {
            html += `
                <tr>
                    <td>${elemento.nombre_producto}</td>
                    <td>${elemento.cantidad}</td>
                    <td>$${elemento.precio_unitario}</td>
                    <td>$${elemento.subtotal}</td>
                </tr>
            `;
        });
        html += `</tbody></table>`;
        contenido.innerHTML = html;

        if (pedido.estado === "pendiente") {
            document.getElementById("boton-cancelar-pedido").style.display = "inline-block";
        }
    } else {
        contenido.innerHTML = `<p class="text-danger">${datos.mensaje}</p>`;
    }
})
.catch(error => {
    const contenido = document.getElementById("contenido-detalles-pedido");
    contenido.innerHTML = `<p class="text-danger">Error al cargar los detalles del pedido.</p>`;
    console.error(`[${new Date().toISOString()}] Error en fetch:`, error);
});

document.getElementById("boton-cancelar-pedido").addEventListener("click", function() {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción cancelará el pedido. No se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DC3545",
        cancelButtonColor: "#6C757D",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No"
    }).then((resultado) => {
        if (resultado.isConfirmed) {
            // Logging
            console.log(`[${new Date().toISOString()}] Cancelando id_pedido: ${id_pedido}`);
            fetch("http://localhost/fory-final/controladores/ControladorCliente/controladorDetallePedido.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `operacion=cancelarPedido&id_pedido=${id_pedido}`
            })
            .then(respuesta => respuesta.json())
            .then(datos => {
                if (datos.exito) {
                    Swal.fire({
                        icon: "success",
                        title: "Pedido Cancelado",
                        text: "El pedido ha sido cancelado exitosamente."
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: datos.mensaje
                    });
                }
            });
        }
    });
});