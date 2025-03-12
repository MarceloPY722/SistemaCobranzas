document.addEventListener('DOMContentLoaded', function() {
    // Obtener el switch de modo oscuro
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    // Verificar si hay una preferencia guardada
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Aplicar el modo oscuro si está guardado
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        if (darkModeToggle) darkModeToggle.checked = true;
    }
    
    // Agregar evento al switch
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'false');
            }
        });
    }
});