<?php
// Configuration sécurisée des sessions
ini_set('session.cookie_httponly', '1'); // Active httponly
ini_set('session.cookie_secure', '1');   // Active secure
ini_set('session.cookie_samesite', 'Strict'); // Configure SameSite
ini_set('session.gc_maxlifetime', 1800); // Session expire après 30 minutes
ini_set('session.use_strict_mode', '1'); // Mode strict pour les sessions
ini_set('session.use_only_cookies', '1'); // Utilise uniquement les cookies pour les sessions

session_start(); // Démarrer la session
session_regenerate_id(true); // Régénérer l'ID après connexion

// Vérifier si l'utilisateur souhaite se déconnecter
if (isset($_GET['logout'])) {
    session_destroy(); // Détruire la session
    header("Location: index.php"); // Rediriger vers la page d'accueil
    exit;
}

try {
    // Chemin vers le fichier de base de données
    $db = new PDO('sqlite:/var/www/html/tasks.db');

    // Définir le mode d'erreur de PDO sur Exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion réussie à la base de données SQLite.";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . htmlspecialchars($e->getMessage()); // Échapper la sortie pour éviter XSS
}

// Fonction pour échapper les sorties et éviter XSS
function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Authentification
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Authentification réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}

// Mise à jour sécurisée d'une tâche
if (isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $new_description = $_POST['new_description'];

    $stmt = $db->prepare("UPDATE tasks SET description = :description WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':description', $new_description, PDO::PARAM_STR);
    $stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Afficher le formulaire de connexion
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion</title>
    </head>
    <body>
        <h1>Connexion</h1>
        <form method="POST" action="">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit" name="login">Se connecter</button>
        </form>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
    </body>
    </html>
    <?php
    exit; // Arrêter l'exécution du script
}

// Récupération des tâches sécurisée
$tasks = [];
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $stmt = $db->prepare("SELECT * FROM tasks WHERE id = :task_id");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Récupérer toutes les tâches si aucun ID n'est spécifié
    $stmt = $db->prepare("SELECT * FROM tasks");
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = :task_id AND user_id = :user_id");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
}

// Ajout sécurisé d'une tâche
if (isset($_POST['add_task'])) {
    $description = $_POST['description'];
    if (!empty($description)) {
        $stmt = $db->prepare("INSERT INTO tasks (description, user_id) VALUES (:description, :user_id)");
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $error = "La description de la tâche ne peut pas être vide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application To-Do List</title>
</head>
<body>
    <h1>Application To-Do List</h1>
  
    <h2>Liste des Tâches</h2>
    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                ID: <?= escape($task['id']); ?> - Description: <?= escape($task['description']); ?>
                <a href="?delete_task=<?= escape($task['id']); ?>">Supprimer</a>
            </li>
        <?php endforeach; ?>
    </ul>


    <h2>Ajouter une tâche</h2>
    <form method="POST" action="">
        <label for="description">Description :</label>
        <input type="text" id="description" name="description" required>
        <button type="submit" name="add_task">Ajouter</button>
    </form>

    <h2>Modifier une tâche</h2>
    <form method="POST" action="">
        <label for="task_id">ID de la tâche :</label>
        <input type="text" id="task_id" name="task_id" required>
        <label for="new_description">Nouvelle description :</label>
        <input type="text" id="new_description" name="new_description" required>
        <button type="submit" name="update_task">Modifier</button>
    </form>

    <h2>Rechercher une tâche par ID</h2>
    <form method="GET" action="">
        <label for="task_id">ID de la tâche :</label>
        <input type="text" id="task_id" name="task_id" required>
        <button type="submit">Rechercher</button>
    </form>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="GET" action="">
            <button type="submit" name="logout">Déconnexion</button>
        </form>
    <?php endif; ?>
</body>
</html>
