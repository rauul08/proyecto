// Obtenemos el botón de alternancia de la barra lateral
const btnToggle = document.querySelector('.toggle-btn');

// Definimos la función que maneja el clic para alternar la barra lateral
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const menu = document.getElementById('menu');
    const promociones = document.getElementById('promociones');
    const conocenos = document.getElementById('conocenos');
    const bienvenida = document.getElementById('bienvenida');

    if (bienvenida !==null) {
        bienvenida.classList.toggle('shift');
    }
    // Verificamos si el elemento menu existe en el DOM y no es null
    if (menu !== null) {
        menu.classList.toggle('shift');
    }
    // Verificamos si el elemento promociones existe en el DOM y no es null
    if (promociones !== null) {
        promociones.classList.toggle('shift');
    }

    if (conocenos !==null) {
        conocenos.classList.toggle('shift');
    }

    // Alternamos la clase active para la barra lateral
    sidebar.classList.toggle('active');
}

// Añadimos el evento de clic al botón de alternancia al cargar la página
if (btnToggle !== null) {
    btnToggle.addEventListener('click', toggleSidebar);
}

// Añadimos el evento de scroll para el botón de alternancia
window.addEventListener('scroll', function() {
    const element = document.querySelector('.toggle-btn');
    if (window.scrollY > 0) {
        element.style.opacity = '0';
        element.removeEventListener('click', toggleSidebar);
        element.style.cursor = 'default';
    } else {
        element.style.opacity = '1';
        element.addEventListener('click', toggleSidebar);
        element.style.cursor = 'pointer';
    }
});