<?php

/**
 * Configuration de l'application Matrello
 */

// Configuration de l'environnement
define('ENVIRONMENT', 'development'); // 'development' ou 'production'

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'matrello');
define('DB_USER', 'root');
define('DB_PASS', 'Super');
define('DB_CHARSET', 'utf8mb4');

// Configuration de l'application
define('APP_NAME', 'Matrello');
define('APP_URL', 'http://matrello.local');
define('BASE_PATH', __DIR__ . '/..');

// Configuration de sécurité
define('SESSION_NAME', 'MATRELLO_SESSION');
define('SESSION_LIFETIME', 1800); // 30 minutes en secondes
define('CSRF_TOKEN_NAME', 'csrf_token');

// Configuration des mots de passe
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_RESET_EXPIRY', 3600); // 1 heure en secondes

// Configuration de l'email (pour réinitialisation de mot de passe)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM_EMAIL', 'noreply@matrello.com');
define('SMTP_FROM_NAME', 'Matrello');

// Configuration des couleurs d'étiquettes
define('LABEL_COLORS', [
    'red' => '#dc3545',
    'orange' => '#fd7e14',
    'yellow' => '#ffc107',
    'green' => '#28a745',
    'blue' => '#007bff'
]);

// Configuration des erreurs
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Europe/Paris');
