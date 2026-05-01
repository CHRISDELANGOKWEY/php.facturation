<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: /facturation/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/config.php';
    
    $identifiant = $_POST['identifiant'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $users = json_decode(file_get_contents(USERS_FILE), true);
    
    foreach ($users as $user) {
        if ($user['identifiant'] === $identifiant && password_verify($password, $user['mot_de_passe']) && $user['actif']) {
            $_SESSION['user'] = [
                'identifiant' => $user['identifiant'],
                'role' => $user['role'],
                'nom_complet' => $user['nom_complet']
            ];
            header('Location: /facturation/index.php');
            exit;
        }
    }
    $error = 'Identifiant ou mot de passe incorrect';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Système de Caisse</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Connexion</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <label>Identifiant :</label>
            <input type="text" name="identifiant" required>
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>