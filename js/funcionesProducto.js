document.addEventListener("DOMContentLoaded", () => {
    cargarCategorias();

    // Configuración de la tabla de productos
    if (document.querySelector("#tablaProductos")) {
        const modalEditarProducto = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
        const modalNuevoProducto = new bootstrap.Modal(document.getElementById('modalNuevoProducto'));
        const modalDetallesProducto = new bootstrap.Modal(document.getElementById('modalDetallesProducto'));

        const grid = new gridjs.Grid({
            columns: [
                { name: 'ID', width: '10%' },
                { name: 'Nombre', width: '20%' },
                { name: 'Descripción', width: '25%' },
                { name: 'Precio', width: '15%', formatter: (cell) => `$${parseFloat(cell).toFixed(2)}` },
                { name: 'Stock', width: '10%' },
                { 
                    name: 'Imagen', 
                    width: '15%',
                    formatter: (cell) => gridjs.html(cell ? `<img src="${cell}" alt="Producto" style="max-width: 100px; max-height: 100px;">` : 'Sin imagen')
                },
                {
                    name: "Acciones",
                    formatter: (_, rows) => gridjs.html(`
                        <button class="btn btn-sm action-btn details-btn ver-detalles" 
                            data-id="${rows.cells[0].data}"
                            title="Ver detalles del producto">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn edit-btn editar" 
                            data-id="${rows.cells[0].data}" 
                            title="Editar producto">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm action-btn delete-btn borrar" 
                            data-id="${rows.cells[0].data}"
                            data-nombre="${rows.cells[1].data}"
                            title="Eliminar producto">
                            <i class="fas fa-trash"></i>
                        </button>
                    `),
                    sort: false,
                    width: '15%'
                }
            ],
            pagination: true,
            search: true,
            sort: true,
            resizable: true,
            server: {
                url: "../controladores/controlador.php?ope1=ListaProductos",
                then: (data) => data.results.map((producto) => [
                    producto.id_producto,
                    producto.nombre_producto,
                    producto.descripcion,
                    producto.precio,
                    producto.stock,
                    producto.imagen
                ])
            },
            language: {
                search: { placeholder: '🔎 Escribe para buscar...' },
                sort: {
                    sortAsc: 'Orden de columna ascendente.',
                    sortDesc: 'Orden de columna descendente.',
                },
                pagination: {
                    previous: '⬅️',
                    next: '➡️',
                    navigate: (page, pages) => `Página ${page} de ${pages}`,
                    page: (page) => `Página ${page}`,
                    showing: '😁 Mostrando del',
                    of: 'de',
                    to: 'al',
                    results: 'registros',
                },
                loading: 'Cargando...',
                noRecordsFound: 'Sin coincidencias encontradas.',
                error: 'Ocurrió un error al obtener los datos.'
            }
        }).render(document.querySelector("#tablaProductos"));

        // Actualizar lista de productos
        document.querySelector("#actListadoProductos").addEventListener("click", (event) => {
            event.preventDefault();
            grid.updateConfig({
                server: {
                    url: "../controladores/controlador.php?ope1=ListaProductos",
                    then: (data) => data.results.map((producto) => [
                        producto.id_producto,
                        producto.nombre_producto,
                        producto.descripcion,
                        producto.precio,
                        producto.stock,
                        producto.imagen
                    ])
                }
            }).forceRender();
        });

        // Acciones en la tabla
        document.querySelector("#tablaProductos").addEventListener("click", (event) => {
            event.preventDefault();
            let ele = event.target.closest('button');

            // Ver detalles del producto
            if (ele?.classList.contains("ver-detalles")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarProducto");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../controladores/controlador.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#detalleID").textContent = xhr.response.id_producto;
                        document.querySelector("#detalleNombre").textContent = xhr.response.nombre_producto;
                        document.querySelector("#detalleDescripcion").textContent = xhr.response.descripcion;
                        document.querySelector("#detallePrecio").textContent = `$${parseFloat(xhr.response.precio).toFixed(2)}`;
                        document.querySelector("#detalleStock").textContent = xhr.response.stock;
                        document.querySelector("#detalleDisponibilidad").textContent = xhr.response.disponibilidad;
                        document.querySelector("#detalleImagen").innerHTML = xhr.response.imagen ? `<img src="${xhr.response.imagen}" alt="Producto" style="max-width: 150px; max-height: 150px;">` : 'Sin imagen';
                        document.querySelector("#detalleTamanoPorcion").textContent = xhr.response.tamano_porcion || 'No especificado';
                        document.querySelector("#detalleCategoria").textContent = xhr.response.nombre_categoria || 'No especificado';
                        modalDetallesProducto.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el producto',
                        });
                    }
                };
            }

            // Editar producto
            if (ele?.classList.contains("editar")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarProducto");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../controladores/controlador.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#editarIDProducto").value = xhr.response.id_producto;
                        document.querySelector("#editarNombreProducto").value = xhr.response.nombre_producto;
                        document.querySelector("#editarDescripcion").value = xhr.response.descripcion;
                        document.querySelector("#editarPrecio").value = xhr.response.precio;
                        document.querySelector("#editarStock").value = xhr.response.stock;
                        document.querySelector("#editarDisponibilidad").value = xhr.response.disponibilidad;
                        document.querySelector("#editarTamanoPorcion").value = xhr.response.tamano_porcion;
                        document.querySelector("#editarCategoria").value = xhr.response.id_categoria;

                        // Mostrar vista previa de la imagen actual
                        let imagenActual = xhr.response.imagen;
                        let previewDiv = document.querySelector("#imagenActualPreview");
                        if (imagenActual) {
                            previewDiv.innerHTML = `<img src="${imagenActual}" alt="Imagen actual" style="max-width: 100px; max-height: 100px;">`;
                        } else {
                            previewDiv.innerHTML = "No hay imagen actual.";
                        }

                        modalEditarProducto.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró el producto',
                        });
                    }
                };
            }

            // Eliminar producto
            if (ele?.classList.contains("borrar")) {
                Swal.fire({
                    title: "¿Está Usted Seguro?",
                    text: "Intenta borrar el producto: " + ele.getAttribute("data-nombre"),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, borrarlo!",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        let id = ele.getAttribute("data-id");
                        let info = new FormData();
                        info.append("ope", "borrarPRO");
                        info.append("id", id);

                        let xhr = new XMLHttpRequest();
                        xhr.open('POST', '../controladores/controlador.php');
                        xhr.send(info);

                        xhr.onload = function () {
                            let response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Borrado',
                                    text: response.message,
                                });
                                document.querySelector("#actListadoProductos").click();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo borrar el producto',
                                });
                            }
                        };
                    }
                });
            }
        });

        // Agregar nuevo producto
        document.querySelector("#btnAgregarProducto").addEventListener("click", function () {
            modalNuevoProducto.show();
            cargarCategorias();
            document.querySelector("#formNuevoProducto").reset();
            document.querySelectorAll('#formNuevoProducto .form-control').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.textContent = '';
                }
            });
        });

        // Guardar nuevo producto con validación
        document.querySelector("#btnGuardarProducto").addEventListener("click", function (event) {
            event.preventDefault();
            if (!validarFormularioProducto()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            let formNuevoProducto = document.querySelector("#formNuevoProducto");
            let formData = new FormData(formNuevoProducto);
            formData.append("ope", "guardarPRO");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "../controladores/controlador.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: response.mensaje,
                    }).then(() => {
                        formNuevoProducto.reset();
                        modalNuevoProducto.hide();
                        document.querySelector("#actListadoProductos").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo guardar el producto',
                    });
                }
            };
            xhr.send(formData);
        });

        // Editar producto con validación
        document.querySelector("#btnEditarProducto").addEventListener("click", function (event) {
            event.preventDefault();
            if (!validarFormularioEdicion()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            let formEditarProducto = document.querySelector("#formEditarProducto");
            let formData = new FormData(formEditarProducto);
            formData.append("ope", "editarPRO");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "../controladores/controlador.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: response.mensaje,
                    }).then(() => {
                        modalEditarProducto.hide();
                        document.querySelector("#actListadoProductos").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo actualizar el producto',
                    });
                }
            };
            xhr.send(formData);
        });

        // Configurar validación en tiempo real para formulario nuevo
        document.getElementById('nombre_producto')?.addEventListener('input', function () {
            validaLargo(this, 3, "El nombre debe tener al menos 3 caracteres");
        });
        document.getElementById('precio')?.addEventListener('input', function () {
            validaMinimo(this, 0.01, "Ingrese un precio válido (mayor que 0)");
        });
        document.getElementById('descripcion')?.addEventListener('input', function () {
            validaLargo(this, 10, "La descripción debe tener al menos 10 caracteres");
        });
        document.getElementById('stock')?.addEventListener('input', function () {
            validaMinimo(this, 0, "Ingrese una cantidad válida (0 o más)");
        });
        document.getElementById('imagen')?.addEventListener('change', function () {
            validaImagen(this, "Por favor, suba una imagen válida (opcional)");
        });
        document.getElementById('id_categoria')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione una categoría");
        });

        // Configurar validación en tiempo real para formulario edición
        document.getElementById('editarNombreProducto')?.addEventListener('input', function () {
            validaLargo(this, 3, "El nombre debe tener al menos 3 caracteres");
        });
        document.getElementById('editarPrecio')?.addEventListener('input', function () {
            validaMinimo(this, 0.01, "Ingrese un precio válido (mayor que 0)");
        });
        document.getElementById('editarDescripcion')?.addEventListener('input', function () {
            validaLargo(this, 10, "La descripción debe tener al menos 10 caracteres");
        });
        document.getElementById('editarStock')?.addEventListener('input', function () {
            validaMinimo(this, 0, "Ingrese una cantidad válida (0 o más)");
        });
        document.getElementById('editarImagen')?.addEventListener('change', function () {
            validaImagen(this, "Por favor, suba una imagen válida (opcional)");
        });
        document.getElementById('editarCategoria')?.addEventListener('change', function () {
            validaSeleccion(this, "Seleccione una categoría");
        });
    }
});

function cargarCategorias() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', '../controladores/controlador.php?ope1=ListaCategorias');
    xhr.onload = function () {
        if (xhr.status === 200) {
            let categorias = JSON.parse(xhr.responseText);
            let selectNuevo = document.getElementById('id_categoria');
            let selectEditar = document.getElementById('editarCategoria');

            selectNuevo.innerHTML = '';
            selectEditar.innerHTML = '';

            categorias.forEach(categoria => {
                let option = document.createElement('option');
                option.value = categoria.id_categoria;
                option.textContent = categoria.nombre_categoria;
                selectNuevo.appendChild(option.cloneNode(true));
                selectEditar.appendChild(option.cloneNode(true));
            });
        }
    };
    xhr.send();
}

// Función de validación de largo mínimo
function validaLargo(campo, min, mensajeError) {
    if (campo.value.length < min) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }
}

// Función de validación de número mínimo
function validaMinimo(campo, min, mensajeError) {
    const valor = parseFloat(campo.value);
    if (isNaN(valor) || valor < min) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }
}

// Función de validación de imagen (opcional)
function validaImagen(campo, mensajeError) {
    if (campo.files.length === 0) {
        campo.classList.remove("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }

    const file = campo.files[0];
    const tiposPermitidos = ["image/jpeg", "image/png", "image/gif"];
    if (!tiposPermitidos.includes(file.type)) {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    }
    campo.classList.remove("is-invalid");
    campo.classList.add("is-valid");
    if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
        campo.nextElementSibling.textContent = "";
    }
    return true;
}

// Función de validación de selección obligatoria
function validaSeleccion(campo, mensajeError) {
    if (campo.value === "") {
        campo.classList.add("is-invalid");
        campo.classList.remove("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = mensajeError;
        }
        return false;
    } else {
        campo.classList.remove("is-invalid");
        campo.classList.add("is-valid");
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('invalid-feedback')) {
            campo.nextElementSibling.textContent = "";
        }
        return true;
    }
}

// Función de validación del formulario de nuevo producto
function validarFormularioProducto() {
    let valido = true;
    if (!validaLargo(document.getElementById('nombre_producto'), 3, "El nombre debe tener al menos 3 caracteres")) valido = false;
    if (!validaMinimo(document.getElementById('precio'), 0.01, "Ingrese un precio válido (mayor que 0)")) valido = false;
    if (!validaLargo(document.getElementById('descripcion'), 10, "La descripción debe tener al menos 10 caracteres")) valido = false;
    if (!validaMinimo(document.getElementById('stock'), 0, "Ingrese una cantidad válida (0 o más)")) valido = false;
    if (!validaImagen(document.getElementById('imagen'), "Por favor, suba una imagen válida (opcional)")) valido = false;
    if (!validaSeleccion(document.getElementById('id_categoria'), "Seleccione una categoría")) valido = false;
    return valido;
}

// Función de validación del formulario de edición de producto
function validarFormularioEdicion() {
    let valido = true;
    if (!validaLargo(document.getElementById('editarNombreProducto'), 3, "El nombre debe tener al menos 3 caracteres")) valido = false;
    if (!validaMinimo(document.getElementById('editarPrecio'), 0.01, "Ingrese un precio válido (mayor que 0)")) valido = false;
    if (!validaLargo(document.getElementById('editarDescripcion'), 10, "La descripción debe tener al menos 10 caracteres")) valido = false;
    if (!validaMinimo(document.getElementById('editarStock'), 0, "Ingrese una cantidad válida (0 o más)")) valido = false;
    if (!validaImagen(document.getElementById('editarImagen'), "Por favor, suba una imagen válida (opcional)")) valido = false;
    if (!validaSeleccion(document.getElementById('editarCategoria'), "Seleccione una categoría")) valido = false;
    return valido;
}