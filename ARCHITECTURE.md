# Architecture de Matrello

## 📁 Structure des dossiers

```
matTrello/
├── app/
│   ├── Controllers/          # Contrôleurs MVC
│   │   ├── AuthController.php      # Authentification (login, register, reset password)
│   │   ├── BoardController.php     # Gestion des tableaux
│   │   ├── CardController.php      # Gestion des cartes
│   │   ├── ListController.php      # Gestion des listes
│   │   └── ProfileController.php   # Profil utilisateur
│   ├── Core/                 # Classes de base
│   │   ├── App.php                 # Routeur principal
│   │   └── Controller.php         # Classe de base des contrôleurs
│   ├── Helpers/              # Helpers
│   │   └── UrlHelper.php           # Fonctions utilitaires pour les URLs
│   └── Views/                # Vues (templates)
│       ├── layouts/
│       │   ├── header.php          # En-tête commun
│       │   └── footer.php          # Pied de page commun
│       ├── auth/
│       │   ├── login.php           # Page de connexion
│       │   ├── register.php        # Page d'inscription
│       │   ├── forgot-password.php # Mot de passe oublié
│       │   └── reset-password.php  # Réinitialisation
│       ├── board/
│       │   ├── index.php           # Liste des tableaux
│       │   ├── show.php            # Affichage d'un tableau
│       │   └── card.php            # Template de carte
│       └── profile/
│           └── index.php           # Profil utilisateur
├── config/                   # Configuration
│   ├── config.php                 # Configuration générale
│   └── database.php               # Connexion à la base de données
├── database/                 # Schéma SQL
│   └── schema.sql                 # Structure de la base de données
├── public/                   # Point d'entrée public
│   ├── index.php                  # Point d'entrée de l'application
│   ├── .htaccess                  # Configuration Apache
│   └── assets/
│       ├── css/
│       │   └── style.css           # Styles personnalisés
│       └── js/
│           ├── main.js             # JavaScript principal
│           └── board.js            # Gestion du tableau (drag & drop)
└── README.md                 # Documentation principale
```

## 🔄 Flux de l'application

### 1. Point d'entrée
- Toutes les requêtes passent par `public/index.php`
- Le fichier `.htaccess` redirige toutes les requêtes vers `index.php`

### 2. Routage
- `App.php` parse l'URL et détermine le contrôleur et la méthode à appeler
- Format d'URL : `/controller/method/param1/param2`

### 3. Contrôleurs
- Héritent de `Controller` (classe de base)
- Gèrent la logique métier
- Appellent les modèles (requêtes SQL directes via PDO)
- Retournent des vues ou des réponses JSON

### 4. Vues
- Templates PHP avec HTML
- Utilisent Bootstrap 5 pour le style
- Incluent le header et footer communs
- Utilisent la fonction `url()` pour générer les liens

## 🗄️ Base de données

### Tables principales
- `users` : Utilisateurs
- `sessions` : Sessions actives (pour déconnexion automatique)
- `password_resets` : Tokens de réinitialisation
- `boards` : Tableaux
- `collaborations` : Partage de tableaux entre utilisateurs
- `lists` : Listes dans les tableaux
- `cards` : Cartes dans les listes
- `card_labels` : Étiquettes des cartes
- `comments` : Commentaires sur les cartes

### Relations
- Un utilisateur peut avoir plusieurs tableaux
- Un tableau peut être partagé avec plusieurs utilisateurs (collaborations)
- Un tableau contient plusieurs listes
- Une liste contient plusieurs cartes
- Une carte peut avoir plusieurs étiquettes et commentaires

## 🔐 Sécurité

### Protection XSS
- Toutes les sorties sont échappées avec `htmlspecialchars()`
- Les entrées sont sanitizées avec `strip_tags()` et `trim()`

### Protection SQL Injection
- Utilisation exclusive de requêtes préparées (PDO)
- Aucune concaténation directe dans les requêtes SQL

### Sessions
- Cookies HTTPOnly et SameSite=Strict
- Expiration après 30 minutes d'inactivité
- Vérification de l'activité à chaque requête

### Validation
- Côté client : HTML5 (required, minlength, type="email")
- Côté serveur : Validation PHP stricte

## 🎨 Frontend

### Technologies
- Bootstrap 5 pour le design responsive
- JavaScript vanilla (pas de framework)
- API HTML5 Drag and Drop native

### Thème clair/sombre
- Géré via variables CSS
- Sauvegarde dans localStorage
- Toggle dans le menu utilisateur

### Drag and Drop
- Implémenté avec l'API HTML5 native
- Gestion des cartes entre listes
- Réorganisation des listes
- Sauvegarde automatique en base de données

## 📤 Export/Import JSON

### Format d'export
```json
{
  "board": {
    "title": "...",
    "description": "...",
    "is_private": true,
    "exported_at": "2024-01-15 10:30:00"
  },
  "lists": [
    {
      "title": "...",
      "position": 0,
      "cards": [
        {
          "title": "...",
          "description": "...",
          "due_date": "2024-01-20",
          "is_completed": false,
          "position": 0,
          "labels": [...],
          "comments": [...]
        }
      ]
    }
  ]
}
```

### Import
- Validation du format JSON
- Vérification de la structure
- Création d'un nouveau tableau avec toutes les données

## 🚀 Déploiement

### Prérequis
- PHP 7.4+
- MySQL/MariaDB
- Serveur web (Apache avec mod_rewrite ou Nginx)

### Configuration
1. Créer la base de données
2. Importer `database/schema.sql`
3. Configurer `config/config.php`
4. Configurer le serveur web pour pointer vers `public/`

### URLs
- Format : `http://domain.com/controller/method/param`
- Exemple : `http://localhost/matTrello/board/show/1`

## 📝 Notes importantes

- Tous les chemins dans les vues utilisent la fonction `url()` du helper
- Les URLs JavaScript utilisent la variable globale `APP_URL`
- Les sessions sont gérées automatiquement par la classe `Controller`
- Les erreurs sont affichées uniquement en mode développement

