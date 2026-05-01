<?php
require_once __DIR__ . '/../auth/session.php';

if (!aRole('manager')) {
    die("Accès réservé aux managers et administrateurs.");
}

$factures = json_decode(file_get_contents(INVOICES_FILE), true);
$aujourdhui = date('Y-m-d');

$facturesJour = [];
$totalHT = 0;
$totalTTC = 0;
$totalTVA = 0;

foreach ($factures as $f) {
    if ($f['date'] === $aujourdhui) {
        $facturesJour[] = $f;
        $totalHT += $f['total_ht'];
        $totalTTC += $f['total_ttc'];
        $totalTVA += $f['tva'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rapport journalier</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main>
    <h1>📅 Rapport journalier</h1>
    <p><strong>Date :</strong> <?= date('d/m/Y') ?></p>

    <?php if (empty($facturesJour)): ?>
        <p>Aucune facture enregistrée aujourd'hui.</p>
    <?php else: ?>
        <h2>Ventes du jour</h2>
        <table border="1">
            <thead>
                <tr><th>ID Facture</th><th>Heure</th><th>Caissier</th><th>Total HT</th><th>TVA</th><th>Total TTC</th></tr>
            </thead>
            <tbody>
            <?php foreach ($facturesJour as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['id_facture']) ?></td>
                <td><?= htmlspecialchars($f['heure']) ?></td>
                <td><?= htmlspecialchars($f['caissier']) ?></td>
                <td><?= number_format($f['total_ht'], 2) ?> CDF</td>
                <td><?= number_format($f['tva'], 2) ?> CDF</td>
                <td><?= number_format($f['total_ttc'], 2) ?> CDF</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h3>📊 Totaux du jour</h3>
        <ul>
            <li><strong>Total HT :</strong> <?= number_format($totalHT, 2) ?> CDF</li>
            <li><strong>Total TVA :</strong> <?= number_format($totalTVA, 2) ?> CDF</li>
            <li><strong>Total TTC :</strong> <?= number_format($totalTTC, 2) ?> CDF</li>
            <li><strong>Nombre de factures :</strong> <?= count($facturesJour) ?></li>
        </ul>
    <?php endif; ?>

    <br>
    <a href="rapport-mensuel.php">📆 Voir rapport mensuel</a><br>
    <a href="/facturation/index.php">🏠 Retour accueil</a>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>