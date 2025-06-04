document.addEventListener('DOMContentLoaded', () => {
  const modulos = {
      dashboard: 'http://localhost/fory-final/php/ModuloAdmin/Dashboard.php', // Módulo agregado
      pedidos: 'http://localhost/fory-final/php/ModuloAdmin/GestionPedidos.php',
      usuarios: 'http://localhost/fory-final/php/ModuloAdmin/GestionUsuarios.php',  // Módulo agregado
      repartidores: 'http://localhost/fory-final/php/ModuloAdmin/GestionRepartidores.php',
      productos: 'http://localhost/fory-final/php/GestionProductos.php',
      categorias: 'http://localhost/fory-final/php/ModuloAdmin/GestionCategorias.php',
      reportes: 'http://localhost/fory-final/php/ModuloAdmin/reportes.php',
      perfil: 'http://localhost/fory-final/php/ModuloAdmin/perfil.php',
      configuracion: 'http://localhost/fory-final/php/ModuloAdmin/configuracion.php'
  };

  const faviconMap = {
      dashboard: 'icons/dashboard.ico',
      pedidos: 'icons/pedidos.ico',
      usuarios: 'icons/usuarios.ico',
      repartidores: 'icons/repartidores.ico',
      productos: 'icons/productos.ico',
      categorias: 'icons/categorias.ico',
      reportes: 'icons/reportes.ico',
      estadisticas: 'icons/estadisticas.ico',
      perfil: 'icons/perfil.ico',
      configuracion: 'icons/configuracion.ico'
  };

  window.showLoading = function() {
      document.getElementById('loading-overlay').style.display = 'flex';
  };

  window.hideLoading = function() {
      document.getElementById('loading-overlay').style.display = 'none';
  };

  // Cambiar favicon dinámicamente
  function cambiarFavicon(modulo) {
      const link = document.querySelector("link[rel~='icon']");
      if (link && faviconMap[modulo]) {
          link.href = faviconMap[modulo];
      }
  }

  // Cargar contenido
  window.cargarContenido = function(modulo) {
      showLoading();
      const iframe = document.getElementById('contenido');
      const titulo = document.getElementById('titulo-seccion');

      if (modulos[modulo]) {
          iframe.src = modulos[modulo];

          cambiarFavicon(modulo);

          // Estilos de botón activo
          document.querySelectorAll('.btn-menu').forEach(btn => {
              btn.classList.remove('active');
              if (btn.getAttribute('data-modulo') === modulo) {
                  btn.classList.add('active');
              }
          });

          // Alertas personalizadas para módulos específicos
          if (modulo === 'reportes') {
              Swal.fire({
                  title: 'Módulo de Reportes',
                  text: 'Aquí puedes generar y descargar reportes.',
                  icon: 'info',
                  timer: 3000,
                  showConfirmButton: false
              });
          }

          if (modulo === 'perfil') {
              Swal.fire({
                  title: 'Perfil de Usuario',
                  text: 'Aquí puedes actualizar tu información personal.',
                  icon: 'success',
                  timer: 3000,
                  showConfirmButton: false
              });
          }
      }
  };

  window.filtrarMenu = function() {
      const input = document.getElementById('buscador').value.toLowerCase();
      const botones = document.querySelectorAll('.btn-menu');
      botones.forEach(btn => {
          const modulo = btn.getAttribute('data-modulo') || btn.textContent.toLowerCase();
          btn.style.display = modulo.includes(input) ? 'flex' : 'none';
      });
  };

  window.salir = function() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas cerrar sesión?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            Swal.fire({
                title: 'Cerrando sesión',
                text: 'Redirigiendo...',
                timer: 1500,
                showConfirmButton: false,
                willClose: () => {
                    window.location.href = 'http://localhost/fory-final/php/salir.php';
                }
            });
        }
    });
};

  window.toggleSidebar = function() {
      document.getElementById('sidebar').classList.toggle('active');
  };

  window.toggleDarkMode = function() {
      document.body.classList.toggle('dark-mode');
      const icon = document.querySelector('.btn-dark-mode i');
      icon.classList.toggle('fa-moon');
      icon.classList.toggle('fa-sun');
      localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
  };

  window.checkNotifications = function() {
      fetch('http://localhost/fory-final/Consultas/check_notifications.php')
          .then(response => response.json())
          .then(data => {
              const count = data.count || 0;
              const notificationCount = document.getElementById('notification-count');
              notificationCount.textContent = count > 0 ? count : '';
              Swal.fire({
                  title: count > 0 ? 'Notificaciones' : 'Sin notificaciones',
                  text: count > 0 ? `Tienes ${count} notificaciones nuevas.` : 'No hay notificaciones nuevas.',
                  icon: 'info',
                  confirmButtonText: 'Aceptar'
              });
          })
          .catch(error => {
              Swal.fire({
                  title: 'Error',
                  text: 'No se pudieron cargar las notificaciones.',
                  icon: 'error'
              });
          });
  };

  // Cargar modo oscuro si ya estaba activado
  if (localStorage.getItem('darkMode') === 'enabled') {
      document.body.classList.add('dark-mode');
      document.querySelector('.btn-dark-mode i').classList.replace('fa-moon', 'fa-sun');
  }

  // Cargar por defecto el dashboard
  cargarContenido('dashboard');
});
