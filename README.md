# Application de Gestion de Tâches

Cette application permet aux utilisateurs de gérer leurs tâches. Les utilisateurs peuvent se connecter, ajouter, modifier et supprimer des tâches.

## Prérequis

- PHP
- SQLite

## Installation

1. **Cloner le dépôt :**

   ```bash
   git clone <URL_DU_DEPOT>
   cd <NOM_DU_DOSSIER>
   ```

2. **Initialiser la base de données :**

   Ouvrez SQLite et exécutez les commandes suivantes :

   ```bash
   sqlite3 tasks.db
   ```

   Ensuite, exécutez les commandes SQL suivantes :

   ```sql
   -- Créer la table users
   CREATE TABLE users (
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       username TEXT NOT NULL UNIQUE,
       password TEXT NOT NULL
   );

   -- Créer la table tasks
   CREATE TABLE tasks (
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       description TEXT NOT NULL,
       user_id INTEGER,
       FOREIGN KEY (user_id) REFERENCES users(id)
   );

   -- Insérer un utilisateur
   INSERT INTO users (username, password) VALUES ('admin', 'hashed_password'); -- Remplacez 'hashed_password' par le mot de passe haché
   ```

   Pour hacher le mot de passe, vous pouvez utiliser PHP :

   ```php
   $password = password_hash('password', PASSWORD_DEFAULT);
   ```

3. **Vérifier la création des tables :**

   Pour vous assurer que les tables ont été créées correctement, exécutez la commande suivante dans SQLite :

   ```sql
   .tables
   ```

4. **Quitter SQLite :**

   Une fois que vous avez terminé, quittez SQLite en tapant :

   ```sql
   .exit
   ```

## Utilisation

1. **Démarrer le serveur PHP :**

   ```bash
   php -S localhost:8080
   ```

2. **Accéder à l'application :**

   Ouvrez votre navigateur et allez à l'adresse suivante :

   ```
   http://localhost:8080
   ```

3. **Fonctionnalités :**

   - Connexion
   - Ajout de tâches
   - Modification de tâches
   - Suppression de tâches
   - Affichage des tâches
   - Déconnexion

## Vulnérabilités

1. **Injection SQL :**


    - Injection SQL dans la requête de connexion
    - Injection SQL dans la requête de suppression de tâche
    - Injection SQL dans la requête d'ajout de tâche
    - Injection SQL dans la requête de modification de tâche

2. **Cross-Site Scripting (XSS) :**

    - XSS dans la description de la tâche
    - XSS dans la requête de connexion
    - XSS dans la requête de suppression de tâche
    - XSS dans la requête d'ajout de tâche
    - XSS dans la requête de modification de tâche
    
