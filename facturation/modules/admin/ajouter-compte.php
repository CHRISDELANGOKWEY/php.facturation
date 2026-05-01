<?php
require_once __DIR__ . '/../../auth/session.php';

if (!aRole('super_admin')) die("Accès refusé.");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant']);
    $password     = $_POST['password'];
    $nom_complet  = trim($_POST['nom_complet']);
    $role         = $_POST['role'];

    $users = json_decode(file_get_contents(USERS_FILE), true);

    // Vérifier doublon
    $existe = false;
    foreach ($users as $u) {
        if ($u['identifiant'] === $identifiant) $existe = true;
    }

    if ($existe) {
        $error = "Identifiant déjà existant.";
    } elseif (empty($identifiant) || empty($password) || empty($nom_complet)) {
        $error = "Tous les champs sont requis.";
    } else {
        $newUser = [
            "identifiant"    => $identifiant,
            "mot_de_passe"   => password_hash($password, PASSWORD_DEFAULT),
            "role"           => $role,
            "nom_complet"    => $nom_complet,
            "date_creation"  => date("Y-m-d"),
            "actif"          => true
        ];
        $users[] = $newUser;
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
        $success = "✅ Compte ajouté avec succès.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un compte</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<main>
    <h1>➕ Ajouter un utilisateur</h1>

    <?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

    <form method="post">
        <label>Identifiant :</label><br>
        <input type="text" name="identifiant" required><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="password" required><br>

        <label>Nom complet :</label><br>
        <input type="text" name="nom_complet" required><br>

        <label>Rôle :</label><br>
        <select name="role">
            <option value="caissier">Caissier</option>
            <option value="manager">Manager</option>
        </select><br><br>

        <button type="submit">Créer le compte</button>
        <a href="gestion-comptes.php">Annuler</a>
    </form>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>