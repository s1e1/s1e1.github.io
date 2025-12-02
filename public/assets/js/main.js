// Gestion du thème clair/sombre
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le thème sauvegardé
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
    
    // Toggle du thème
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.body.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Sauvegarder dans la session (optionnel)
            fetch('<?= APP_URL ?>/api/theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ theme: newTheme })
            }).catch(() => {}); // Ignorer les erreurs
        });
    }
});

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        const text = themeToggle.querySelector('span');
        if (icon && text) {
            if (theme === 'dark') {
                icon.className = 'bi bi-sun';
                text.textContent = 'Thème clair';
            } else {
                icon.className = 'bi bi-moon-stars';
                text.textContent = 'Thème sombre';
            }
        }
    }
}

// Fonction utilitaire pour les requêtes AJAX
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        return { error: 'Erreur de connexion' };
    }
}

// Gestion des messages flash
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        setTimeout(() => bsAlert.close(), 5000);
    });
}, 100);

