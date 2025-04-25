document.addEventListener("DOMContentLoaded", () => {
    // Configuración de la tabla de categorías
    if (document.querySelector("#tablaCategorias")) {
        const modalEditarCategoria = new bootstrap.Modal(document.getElementById('modalEditarCategoria'));
        const modalNuevaCategoria = new bootstrap.Modal(document.getElementById('modalNuevaCategoria'));

        const grid = new gridjs.Grid({
            columns: [
                { name: 'ID', width: '10%' },
                { name: 'Nombre', width: '30%' },
                { name: 'Descripción', width: '40%' },
                {
                    name: "Acciones",
                    formatter: (_, rows) => gridjs.html(`
                        <style>
                            .btn {
                                background-color: white;
                                color: rgb(136, 176, 219);
                                border: 1px solid black;
                            }
                            .btn:hover {
                                background-color: rgb(136, 176, 250);
                                color: white;
                            }
                        </style>
                        <button class="btn btn-info btn-sm editar" 
                            data-id="${rows.cells[0].data}" 
                            data-nombre="${rows.cells[1].data}" 
                            data-descripcion="${rows.cells[2].data}">✍</button>
                        <button class="btn btn-info btn-sm borrar" 
                            data-id="${rows.cells[0].data}"
                            data-nombre="${rows.cells[1].data}">🗑</button>
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
                url: "../controladores/controladorCat.php?ope1=ListaCategorias",
                then: (data) => data.results.map((categoria) => [
                    categoria.id_categoria,
                    categoria.nombre_categoria,
                    categoria.descripcion
                ])
            },
            language: {
                search: { placeholder: '🔎Escribe para buscar...' },
                pagination: {
                    previous: '⬅️',
                    next: '➡️',
                    navigate: (page, pages) => `Página ${page} de ${pages}`,
                    showing: '😁 Mostrando del',
                    of: 'de',
                    to: 'al',
                    results: 'registros',
                },
                loading: 'Cargando...',
                noRecordsFound: 'Sin coincidencias encontradas.',
                error: 'Ocurrió un error al obtener los datos.'
            }
        }).render(document.querySelector("#tablaCategorias"));

        // Actualizar lista de categorías
        document.querySelector("#actListadoCategorias").addEventListener("click", (event) => {
            event.preventDefault();
            grid.updateConfig({
                server: {
                    url: "../controladores/controladorCat.php?ope1=ListaCategorias",
                    then: (data) => data.results.map((categoria) => [
                        categoria.id_categoria,
                        categoria.nombre_categoria,
                        categoria.descripcion
                    ])
                }
            }).forceRender();
        });

        // Edición y eliminación de categoría
        document.querySelector("#tablaCategorias").addEventListener("click", (event) => {
            event.preventDefault();
            let ele = event.target;

            // Editar categoría
            if (ele.classList.contains("editar")) {
                let id = ele.getAttribute("data-id");
                let info = new FormData();
                info.append("ope", "buscarCategoria");
                info.append("id", id);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../controladores/controladorCat.php');
                xhr.responseType = "json";
                xhr.send(info);

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.response && xhr.response.success) {
                        document.querySelector("#editarIDCategoria").value = xhr.response.id_categoria;
                        document.querySelector("#editarNombreCategoria").value = xhr.response.nombre_categoria;
                        document.querySelector("#editarDescripcion").value = xhr.response.descripcion;
                        modalEditarCategoria.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.response?.mensaje || 'No se encontró la categoría',
                        });
                    }
                };

                xhr.onerror = function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Algo salió mal',
                    });
                };
            }

            // Eliminar categoría
            if (ele.classList.contains("borrar")) {
                Swal.fire({
                    title: "¿Está Usted Seguro?",
                    text: "Intenta borrar la categoría: " + ele.getAttribute("data-nombre"),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, borrarla!",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        let id = ele.getAttribute("data-id");
                        let info = new FormData();
                        info.append("ope", "borrarCAT");
                        info.append("id", id);

                        let xhr = new XMLHttpRequest();
                        xhr.open('POST', '../controladores/controladorCat.php');
                        xhr.send(info);

                        xhr.onload = function () {
                            let response = JSON.parse(xhr.responseText);
                            if (response.success === true) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Borrado',
                                    text: response.message,
                                });
                                document.querySelector("#actListadoCategorias").click();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo borrar la categoría',
                                });
                            }
                        };
                    }
                });
            }
        });

        // Agregar nueva categoría
        document.querySelector("#btnAgregarCategoria").addEventListener("click", function () {
            modalNuevaCategoria.show();
            document.querySelector("#formNuevaCategoria").reset();
            document.querySelectorAll('#formNuevaCategoria .form-control').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.textContent = '';
                }
            });
        });

        // Guardar nueva categoría con validación
        document.querySelector("#btnGuardarCategoria").addEventListener("click", function (event) {
            event.preventDefault();

            if (!validarFormularioCategoria()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formNuevaCategoria = document.querySelector("#formNuevaCategoria");
            let formData = new FormData(formNuevaCategoria);
            formData.append("ope", "guardarCAT");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "../controladores/controladorCat.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: response.mensaje,
                    }).then(() => {
                        formNuevaCategoria.reset();
                        modalNuevaCategoria.hide();
                        document.querySelector("#actListadoCategorias").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo guardar la categoría',
                    });
                }
            };
            xhr.send(formData);
        });

        // Editar categoría con validación
        document.querySelector("#btnEditarCategoria").addEventListener("click", function (event) {
            event.preventDefault();

            if (!validarFormularioEdicion()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor complete todos los campos requeridos correctamente',
                });
                return;
            }

            let formEditarCategoria = document.querySelector("#formEditarCategoria");
            let formData = new FormData(formEditarCategoria);
            formData.append("ope", "editarCAT");

            let xhr = new XMLHttpRequest();
            xhr.open('POST', "../controladores/controladorCat.php", true);
            xhr.onload = function () {
                let response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: response.mensaje,
                    }).then(() => {
                        modalEditarCategoria.hide();
                        document.querySelector("#actListadoCategorias").click();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo actualizar la categoría',
                    });
                }
            };
            xhr.send(formData);
        });

        // Validaciones en tiempo real
        document.getElementById('nombre_categoria')?.addEventListener('input', function () {
            validaLargo(this, 3, "El nombre debe tener al menos 3 caracteres");
        });

        document.getElementById('descripcion')?.addEventListener('input', function () {
            validaLargo(this, 10, "La descripción debe tener al menos 10 caracteres");
        });

        document.getElementById('editarNombreCategoria')?.addEventListener('input', function () {
            validaLargo(this, 3, "El nombre debe tener al menos 3 caracteres");
        });

        document.getElementById('editarDescripcion')?.addEventListener('input', function () {
            validaLargo(this, 10, "La descripción debe tener al menos 10 caracteres");
        });
    }
});

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

// Validación del formulario de nueva categoría
function validarFormularioCategoria() {
    let valido = true;
    if (!validaLargo(document.getElementById('nombre_categoria'), 3, "El nombre debe tener al menos 3 caracteress")) valido = false;
    if (!validaLargo(document.getElementById('descripcion'), 10, "La descripción debe tener al menos 10 caracteres")) valido = false;
    return valido;
}

// Validación del formulario de edición
function validarFormularioEdicion() {
    let valido = true;
    if (!validaLargo(document.getElementById('editarNombreCategoria'), 3, "El nombre debe tener al menos 3 caracteres")) valido = false;
    if (!validaLargo(document.getElementById('editarDescripcion'), 10, "La descripción debe tener al menos 10 caracteres")) valido = false;
    return valido;
}
