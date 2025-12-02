# Guide d'installation - Matrello

## 📋 Prérequis

- **PHP** : Version 7.4 ou supérieure
- **MySQL/MariaDB** : Version 5.7 ou supérieure
- **Serveur web** : Apache (avec mod_rewrite) ou Nginx
- **Extensions PHP** : PDO, PDO_MySQL, mbstring

## 🚀 Installation étape par étape

### 1. Télécharger le projet

```bash
cd /var/www/html  # ou votre répertoire web
# Télécharger ou cloner le projet dans matTrello/
```

### 2. Configurer la base de données

#### Créer la base de données

```sql
CREATE DATABASE matrello CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Importer le schéma

```bash
mysql -u root -p matrello < database/schema.sql
```

Ou via phpMyAdmin :

- Sélectionner la base de données `matrello`
- Aller dans l'onglet "Importer"
- Choisir le fichier `database/schema.sql`

### 3. Configurer l'application

Éditer le fichier `config/config.php` :

```php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'matrello');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');

// Configuration de l'application
define('APP_URL', 'http://localhost/matTrello'); // Adapter selon votre installation
```

### 4. Configurer le serveur web

#### Apache

Assurez-vous que `mod_rewrite` est activé :

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configurez le VirtualHost pour pointer vers le dossier `public/` :

```apache
<VirtualHost *:80>
    ServerName matrello.local
    DocumentRoot /chemin/vers/matTrello/public

    <Directory /chemin/vers/matTrello/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

Configuration exemple :

```nginx
server {
    listen 80;
    server_name matrello.local;
    root /chemin/vers/matTrello/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 5. Permissions (Linux/Mac)

```bash
chmod -R 755 public/
chmod -R 755 app/
chmod -R 755 config/
```

### 6. Configuration de l'email (optionnel)

Pour activer la réinitialisation de mot de passe par email, configurez dans `config/config.php` :

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre-email@gmail.com');
define('SMTP_PASS', 'votre-mot-de-passe');
define('SMTP_FROM_EMAIL', 'noreply@matrello.com');
define('SMTP_FROM_NAME', 'Matrello');
```

**Note** : L'envoi d'email nécessite une implémentation supplémentaire dans `AuthController.php`.

### 7. Tester l'installation

1. Accéder à `http://localhost/matTrello` (ou votre URL configurée)
2. Créer un compte utilisateur
3. Se connecter
4. Créer un tableau de test

## 🔧 Dépannage

### Erreur 404 sur toutes les pages

- Vérifier que `mod_rewrite` est activé (Apache)
- Vérifier la configuration du serveur web
- Vérifier que le `.htaccess` est présent dans `public/`

### Erreur de connexion à la base de données

- Vérifier les identifiants dans `config/config.php`
- Vérifier que MySQL/MariaDB est démarré
- Vérifier que la base de données existe

### Erreur "Class not found"

- Vérifier que tous les fichiers sont présents
- Vérifier les chemins dans les `require_once`

### Les styles ne s'affichent pas

- Vérifier que le chemin `/public/assets/css/style.css` est accessible
- Vérifier la configuration de `APP_URL`

## 📝 Notes

- En mode développement, les erreurs PHP sont affichées
- En mode production, changez `ENVIRONMENT` à `'production'` dans `config/config.php`
- Les sessions sont stockées en base de données pour la déconnexion automatique

## 🆘 Support

En cas de problème, vérifier :

1. Les logs PHP (`/var/log/php/error.log` ou équivalent)
2. Les logs du serveur web
3. La console du navigateur (F12) pour les erreurs JavaScript
