<?php
require_once __DIR__ . '/auth/session.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Accueil - Système de Caisse</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main>
        <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['user']['nom_complet']); ?></h1>
        <p>Rôle : <?php echo htmlspecialchars($_SESSION['user']['role']); ?></p>
        
        <div class="menu">
            <h2>Menu principal</h2>
            <ul>
                <li><a href="/facturation/modules/facturation/nouvelle-facture.php">Nouvelle facture</a></li>
                <?php if (aRole('manager')): ?>
                    <li><a href="/facturation/modules/produits/enregistrer.php">Enregistrer un produit</a></li>
                    <li><a href="/facturation/modules/produits/liste.php">Liste des produits</a></li>
                    <li><a href="/facturation/rapports/rapport-journalier.php">Rapport journalier</a></li>
                    <li><a href="/facturation/rapports/rapport-mensuel.php">Rapport mensuel</a></li>
                <?php endif; ?>
                <?php if (aRole('super_admin')): ?>
                    <li><a href="/facturation/modules/admin/gestion-comptes.php">Gestion des comptes</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>