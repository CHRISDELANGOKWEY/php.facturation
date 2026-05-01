<?php
require_once __DIR__ . '/../../auth/session.php';

// Seuls Manager et Super Admin peuvent voir la liste
if (!aRole('manager')) {
    die('Accès non autorisé');
}

$produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liste des produits</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <main>
        <h1>Liste des produits</h1>
        
        <?php if (empty($produits)): ?>
            <p>Aucun produit enregistré.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Code-barres</th>
                        <th>Nom</th>
                        <th>Prix HT (CDF)</th>
                        <th>Stock</th>
                        <th>Date expiration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['code_barre']); ?></td>
                        <td><?php echo htmlspecialchars($p['nom']); ?></td>
                        <td><?php echo number_format($p['prix_unitaire_ht'], 2); ?></td>
                        <td><?php echo $p['quantite_stock']; ?></td>
                        <td><?php echo htmlspecialchars($p['date_expiration']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="/facturation/modules/produits/enregistrer.php">Enregistrer un nouveau produit</a>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>