<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | Gestor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css1.css">
</head>
<body>
<!-- Add this button somewhere in your header navbar -->
<button class="btn btn-sm dark-mode-toggle" id="darkModeToggle">
    <i class="bi bi-moon-stars"></i>
</button>

<script>
document.getElementById('darkModeToggle').addEventListener('click', function() {
    window.dispatchEvent(new Event('darkModeToggle'));
    
    // Toggle icon
    const icon = this.querySelector('i');
    if (document.body.classList.contains('dark-mode')) {
        icon.classList.remove('bi-sun');
        icon.classList.add('bi-moon-stars');
    } else {
        icon.classList.remove('bi-moon-stars');
        icon.classList.add('bi-sun');
    }
});

// Initialize icon based on current mode
document.addEventListener('DOMContentLoaded', function() {
    const icon = document.querySelector('#darkModeToggle i');
    if (localStorage.getItem('darkMode') === 'true') {
        icon.classList.remove('bi-moon-stars');
        icon.classList.add('bi-sun');
    }
});
</script>
</body>
</html>