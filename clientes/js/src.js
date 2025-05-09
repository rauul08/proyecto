function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const container = document.getElementById('container');
    sidebar.classList.toggle('show');
    container.classList.toggle('shift');
}
