<?php
require_once __DIR__ . '/../auth/session.php';

if (!aRole('manager')) {
    die("Accès réservé aux managers et administrateurs.");
}

$factures = json_decode(file_get_contents(INVOICES_FILE), true);

// Mois sélectionné (par défaut : mois en cours)
$moisChoisi = $_GET['mois'] ?? date('Y-m');
$annee = substr($moisChoisi, 0, 4);
$mois = substr($moisChoisi, 5, 2);

$facturesMois = [];
$totalHT = 0;
$totalTTC = 0;
$totalTVA = 0;

foreach ($factures as $f) {
    $dateFacture = substr($f['date'], 0, 7); // YYYY-MM
    if ($dateFacture === $moisChoisi) {
        $facturesMois[] = $f;
        $totalHT += $f['total_ht'];
        $totalTTC += $f['total_ttc'];
        $totalTVA += $f['tva'];
    }
}

// Liste des mois disponibles
$moisDisponibles = [];
foreach ($factures as $f) {
    $moisDisponibles[substr($f['date'], 0, 7)] = true;
}
ksort($moisDisponibles);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rapport mensuel</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main>
    <h1>📆 Rapport mensuel</h1>

    <form method="get">
        <label>Choisir le mois :</label>
        <select name="mois">
            <?php foreach (array_keys($moisDisponibles) as $m): ?>
                <option value="<?= $m ?>" <?= $m === $moisChoisi ? 'selected' : '' ?>>
                    <?= date('F Y', strtotime($m . '-01')) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Afficher</button>
    </form>

    <h2>Ventes du mois : <?= date('F Y', strtotime($moisChoisi . '-01')) ?></h2>

    <?php if (empty($facturesMois)): ?>
        <p>Aucune facture pour ce mois.</p>
    <?php else: ?>
        <table border="1">
            <thead>
                <tr><th>Date</th><th>ID Facture</th><th>Caissier</th><th>Total HT</th><th>TVA</th><th>Total TTC</th></tr>
            </thead>
            <tbody>
            <?php foreach ($facturesMois as $f): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($f['date'])) ?></td>
                <td><?= htmlspecialchars($f['id_facture']) ?></td>
                <td><?= htmlspecialchars($f['caissier']) ?></td>
                <td><?= number_format($f['total_ht'], 2) ?> CDF</td>
                <td><?= number_format($f['tva'], 2) ?> CDF</td>
                <td><?= number_format($f['total_ttc'], 2) ?> CDF</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h3>📊 Totaux du mois</h3>
        <ul>
            <li><strong>Total HT :</strong> <?= number_format($totalHT, 2) ?> CDF</li>
            <li><strong>Total TVA :</strong> <?= number_format($totalTVA, 2) ?> CDF</li>
            <li><strong>Total TTC :</strong> <?= number_format($totalTTC, 2) ?> CDF</li>
            <li><strong>Nombre de factures :</strong> <?= count($facturesMois) ?></li>
        </ul>
    <?php endif; ?>

    <br>
    <a href="rapport-journalier.php">📅 Voir rapport journalier</a><br>
    <a href="/facturation/index.php">🏠 Retour accueil</a>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>