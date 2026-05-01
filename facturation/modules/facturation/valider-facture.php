<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: /facturation/auth/login.php');
    exit;
}

if (empty($_SESSION['panier'])) {
    header('Location: nouvelle-facture.php');
    exit;
}

// 1. Mettre à jour les stocks
$produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
$panier = $_SESSION['panier'];
$stockOK = true;

foreach ($panier as $code => $item) {
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $code) {
            // ✅ CORRECTION ICI : conversion en int
            if ((int)$p['quantite_stock'] >= (int)$item['quantite']) {
                $p['quantite_stock'] = (int)$p['quantite_stock'] - (int)$item['quantite'];
            } else {
                $stockOK = false;
            }
            break;
        }
    }
}

if (!$stockOK) {
    die("❌ Erreur : stock insuffisant pour un produit.");
}

file_put_contents(PRODUCTS_FILE, json_encode($produits, JSON_PRETTY_PRINT));

// 2. Sauvegarder la facture
$factures = json_decode(file_get_contents(INVOICES_FILE), true);

$totalHT = 0;
foreach ($panier as $item) {
    $totalHT += $item['sous_total'];
}
$tva = round($totalHT * 0.18, 2);
$ttc = $totalHT + $tva;

$id = "FAC-" . date("Ymd") . "-" . str_pad(count($factures) + 1, 3, "0", STR_PAD_LEFT);

$facture = [
    "id_facture" => $id,
    "date" => date("Y-m-d"),
    "heure" => date("H:i:s"),
    "caissier" => $_SESSION['user']['identifiant'],
    "articles" => [],
    "total_ht" => $totalHT,
    "tva" => $tva,
    "total_ttc" => $ttc
];

foreach ($panier as $code => $item) {
    $facture["articles"][] = [
        "code_barre" => $code,
        "nom" => $item["nom"],
        "prix_unitaire_ht" => $item["prix"],
        "quantite" => $item["quantite"],
        "sous_total_ht" => $item["sous_total"]
    ];
}

$factures[] = $facture;
file_put_contents(INVOICES_FILE, json_encode($factures, JSON_PRETTY_PRINT));

// 3. Vider le panier
$_SESSION['panier'] = [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Facture validée</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<main>
    <h2>✅ Facture validée</h2>
    <p>ID : <strong><?= $id ?></strong></p>
    <p>Date : <?= date('d/m/Y H:i:s') ?></p>
    <p>Caissier : <?= $_SESSION['user']['identifiant'] ?></p>
    <p>Total TTC : <?= number_format($ttc, 2) ?> CDF</p>
    <a href="nouvelle-facture.php">🔄 Nouvelle facture</a><br>
    <a href="/facturation/index.php">🏠 Accueil</a>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>