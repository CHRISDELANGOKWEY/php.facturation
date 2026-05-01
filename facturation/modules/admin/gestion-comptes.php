<?php
require_once __DIR__ . '/../../auth/session.php';

// Seul SUPER_ADMIN peut accéder
if (!aRole('super_admin')) {
    die("Accès réservé au Super Administrateur.");
}

$users = json_decode(file_get_contents(USERS_FILE), true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des comptes</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<main>
    <h1>👥 Gestion des comptes utilisateurs</h1>

    <a href="ajouter-compte.php">➕ Ajouter un compte</a>

    <table>
        <thead>
            <tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Date création</th><th>Actif</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['identifiant']) ?></td>
                <td><?= htmlspecialchars($u['nom_complet']) ?></td>
                <td><?= $u['role'] ?></td>
                <td><?= $u['date_creation'] ?></td>
                <td><?= $u['actif'] ? '✅' : '❌' ?></td>
                <td>
                    <?php if ($u['identifiant'] !== $_SESSION['user']['identifiant']): ?>
                        <a href="supprimer-compte.php?identifiant=<?= urlencode($u['identifiant']) ?>" 
                           onclick="return confirm('Supprimer ce compte ?')">🗑️ Supprimer</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>