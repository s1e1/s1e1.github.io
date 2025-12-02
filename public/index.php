<?php
/**
 * Point d'entrée de l'application Matrello
 */

// Charger la configuration
require_once '../config/config.php';

// Charger la connexion à la base de données
require_once '../config/database.php';

// Charger le contrôleur de base
require_once '../app/Core/Controller.php';

// Charger l'application
require_once '../app/Core/App.php';

// Démarrer l'application
$app = new App();

