// Obtenemos el botón de alternancia de la barra lateral
const btnToggle = document.querySelector('.toggle-btn');

// Definimos la función que maneja el clic para alternar la barra lateral
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const conocenosContainer = document.getElementById('conocenos-container');
    const background = document.querySelector('.background');

    // Alternamos la clase active para la barra lateral
    sidebar.classList.toggle('active');

    // Alternamos la clase shift para el contenedor conocenos
    if (conocenosContainer !== null) {
        conocenosContainer.classList.toggle('shift');
    }

    // Alternamos la clase shift para el background
    if (background !== null) {
        background.classList.toggle('shift');
    }
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
