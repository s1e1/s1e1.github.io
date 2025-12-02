# Matrello - Application de gestion de projet

Matrello est une application web de gestion de projet inspirée de Trello, permettant de visualiser l'avancement des tâches via des tableaux Kanban.

## 📋 Fonctionnalités

### Authentification & Sécurité
- ✅ Création de compte utilisateur (nom, e-mail, mot de passe)
- ✅ Connexion / déconnexion sécurisée via sessions PHP
- ✅ Réinitialisation de mot de passe par e-mail (lien temporaire)
- ✅ Hachage des mots de passe (password_hash)
- ✅ Déconnexion automatique après 30 minutes d'inactivité
- ✅ Validations côté client (HTML5 + JS) et côté serveur (PHP)

### Gestion des projets (tableaux)
- ✅ Créer, renommer, supprimer plusieurs tableaux par utilisateur
- ✅ Tableaux privés par défaut, partageable avec d'autres utilisateurs
- ✅ Exporter un tableau au format JSON
- ✅ Importer un tableau depuis un fichier JSON

### Gestion des listes et cartes
- ✅ Ajouter, modifier, supprimer des listes dans un tableau
- ✅ Ajouter, modifier, supprimer des cartes dans une liste
- ✅ Déplacer des cartes entre listes via Drag and Drop (HTML5)
- ✅ Réorganiser l'ordre des listes via Drag and Drop
- ✅ Chaque carte contient :
  - Titre
  - Description (texte long)
  - Date limite (optionnelle)
  - Étiquettes colorées (5 couleurs prédéfinies)
  - Commentaires (avec auteur et horodatage)
  - Statut "terminée"

### Interface utilisateur
- ✅ Interface responsive (mobile, tablette, desktop)
- ✅ Thème clair / sombre basculable
- ✅ Indication visuelle pour les cartes :
  - En retard (date limite dépassée → rouge)
  - Imminente (échéance dans ≤ 24h → orange)
- ✅ Barre de recherche / filtre :
  - Trouver des cartes par titre
  - Filtrer par date d'échéance

### Gestion du profil
- ✅ Modifier le nom, l'e-mail et le mot de passe
- ✅ Supprimer son compte (efface toutes les données)

## 🛠️ Stack technique

- **Backend** : PHP en architecture MVC
- **Frontend** : HTML5, CSS3, Bootstrap 5, JavaScript vanilla
- **Base de données** : MySQL/MariaDB
- **Authentification** : Sessions PHP
- **Drag and Drop** : API native HTML5 Drag and Drop
- **Sécurité** : Protection XSS, CSRF, validation stricte

## 📁 Structure du projet

```
matTrello/
├── app/
│   ├── Controllers/          # Contrôleurs MVC
│   │   ├── AuthController.php
│   │   ├── BoardController.php
│   │   ├── CardController.php
│   │   ├── ListController.php
│   │   └── ProfileController.php
│   ├── Core/                 # Classes de base
│   │   ├── App.php
│   │   └── Controller.php
│   └── Views/                # Vues
│       ├── layouts/
│       ├── auth/
│       ├── board/
│       └── profile/
├── config/                   # Configuration
│   ├── config.php
│   └── database.php
├── database/                 # Schéma SQL
│   └── schema.sql
├── public/                   # Point d'entrée public
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       └── js/
└── README.md
```

## 🚀 Installation

### Prérequis
- PHP 7.4 ou supérieur
- MySQL/MariaDB
- Serveur web (Apache avec mod_rewrite ou Nginx)
- Extension PDO pour PHP

### Étapes d'installation

1. **Cloner ou télécharger le projet**
   ```bash
   cd matTrello
   ```

2. **Configurer la base de données**
   - Créer une base de données MySQL :
     ```sql
     CREATE DATABASE matrello CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   - Importer le schéma :
     ```bash
     mysql -u root -p matrello < database/schema.sql
     ```

3. **Configurer l'application**
   - Éditer `config/config.php` :
     - Modifier les constantes de base de données (DB_HOST, DB_NAME, DB_USER, DB_PASS)
     - Modifier `APP_URL` avec l'URL de votre installation
     - Configurer l'envoi d'email pour la réinitialisation de mot de passe (SMTP_*)

4. **Configurer le serveur web**

   **Apache** : Assurez-vous que mod_rewrite est activé et que le DocumentRoot pointe vers le dossier `public/`.

   **Nginx** : Configuration exemple :
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

5. **Permissions**
   ```bash
   chmod -R 755 public/
   ```

## 📊 Schéma de base de données

Le schéma comprend les tables suivantes :
- `users` - Utilisateurs
- `sessions` - Sessions actives
- `password_resets` - Tokens de réinitialisation
- `boards` - Tableaux
- `collaborations` - Partage de tableaux
- `lists` - Listes
- `cards` - Cartes
- `card_labels` - Étiquettes des cartes
- `comments` - Commentaires

Voir `database/schema.sql` pour le schéma complet.

## 🔒 Règles de validation

### Côté serveur (PHP)

**Email** :
- Format valide (FILTER_VALIDATE_EMAIL)
- Unique dans la base de données

**Mot de passe** :
- Minimum 8 caractères (PASSWORD_MIN_LENGTH)
- Vérification de correspondance pour confirmation

**Nom** :
- Minimum 2 caractères
- Sanitisation HTML

**Titre de tableau/liste/carte** :
- Non vide
- Sanitisation HTML

**Date d'échéance** :
- Format YYYY-MM-DD
- Validation de date valide

**Couleurs d'étiquettes** :
- Doit être une des 5 couleurs prédéfinies (red, orange, yellow, green, blue)

### Côté client (HTML5 + JavaScript)

**HTML5** :
- `required` sur les champs obligatoires
- `type="email"` pour les emails
- `minlength` pour les longueurs minimales
- `pattern` si nécessaire

**JavaScript** :
- Vérification de correspondance des mots de passe
- Validation avant soumission de formulaire
- Messages d'erreur utilisateur

## 📤 Export / Import JSON

### Format d'export

Le format JSON exporté suit cette structure :

```json
{
  "board": {
    "title": "Nom du tableau",
    "description": "Description",
    "is_private": true,
    "exported_at": "2024-01-15 10:30:00"
  },
  "lists": [
    {
      "title": "Nom de la liste",
      "position": 0,
      "cards": [
        {
          "title": "Titre de la carte",
          "description": "Description",
          "due_date": "2024-01-20",
          "is_completed": false,
          "position": 0,
          "labels": [
            {
              "color": "#dc3545",
              "label": "Urgent"
            }
          ],
          "comments": [
            {
              "content": "Commentaire",
              "user_name": "Nom utilisateur",
              "created_at": "2024-01-15 10:00:00"
            }
          ]
        }
      ]
    }
  ]
}
```

### Import

L'import vérifie :
- Validité du JSON
- Présence des champs obligatoires (`board`, `lists`)
- Structure correcte des données

Les données importées créent un nouveau tableau avec toutes les listes et cartes.

## 🔐 Sécurité

### Protection XSS
- Échappement de toutes les sorties avec `htmlspecialchars()`
- Sanitisation des entrées avec `strip_tags()` et `trim()`

### Protection CSRF
- Tokens CSRF pour les formulaires critiques (à implémenter si nécessaire)
- Validation des tokens côté serveur

### Protection SQL Injection
- Utilisation exclusive de requêtes préparées (PDO)
- Pas de concaténation directe dans les requêtes SQL

### Sessions
- Cookies HTTPOnly
- SameSite=Strict
- Expiration après 30 minutes d'inactivité
- Nettoyage automatique des sessions expirées

## 🎨 Thème clair/sombre

Le thème est géré via :
- Variables CSS (`:root` et `[data-theme="dark"]`)
- Toggle dans le menu utilisateur
- Sauvegarde dans localStorage
- Transition fluide entre les thèmes

## 📝 TODO / Améliorations futures

- [ ] Implémentation complète de l'envoi d'email pour réinitialisation
- [ ] Modal de détail de carte avec édition complète
- [ ] Partage de tableaux avec d'autres utilisateurs (interface)
- [ ] Notifications en temps réel
- [ ] Historique des actions
- [ ] Pièces jointes aux cartes
- [ ] Templates de tableaux

## 📄 Licence

Ce projet est un exemple éducatif.

## 👤 Support

Pour toute question ou problème, consultez la documentation ou ouvrez une issue.

