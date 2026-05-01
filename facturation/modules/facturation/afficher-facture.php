<?php
require_once __DIR__ . '/../../auth/session.php';

$factureId = $_GET['id'] ?? '';

$factures = json_decode(file_get_contents(INVOICES_FILE), true);
$facture = null;

foreach ($factures as $f) {
    if ($f['id_facture'] === $factureId) {
        $facture = $f;
        break;
    }
}

if (!$facture) {
    die("Facture non trouvée.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Facture <?= htmlspecialchars($factureId) ?></title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<main>
    <h1>🧾 Facture détaillée</h1>

    <div class="facture-info">
        <p><strong>ID :</strong> <?= htmlspecialchars($facture['id_facture']) ?></p>
        <p><strong>Date :</strong> <?= $facture['date'] ?> à <?= $facture['heure'] ?></p>
        <p><strong>Caissier :</strong> <?= htmlspecialchars($facture['caissier']) ?></p>
    </div>

    <h2>Articles</h2>
    <table>
        <thead>
            <tr><th>Désignation</th><th>Prix unitaire HT</th><th>Qté</th><th>Sous-total HT</th></tr>
        </thead>
        <tbody>
        <?php foreach ($facture['articles'] as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['nom']) ?></td>
            <td><?= number_format($a['prix_unitaire_ht'], 2) ?> CDF</td>
            <td><?= $a['quantite'] ?></td>
            <td><?= number_format($a['sous_total_ht'], 2) ?> CDF</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totaux">
        <p><strong>Total HT :</strong> <?= number_format($facture['total_ht'], 2) ?> CDF</p>
        <p><strong>TVA (18%) :</strong> <?= number_format($facture['tva'], 2) ?> CDF</p>
        <p><strong>Net à payer :</strong> <?= number_format($facture['total_ttc'], 2) ?> CDF</p>
    </div>

    <a href="javascript:window.print()">🖨️ Imprimer</a> |
    <a href="/facturation/index.php">🏠 Retour accueil</a>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>