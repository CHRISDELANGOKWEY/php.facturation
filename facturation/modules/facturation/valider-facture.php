<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

// ==================== DIAGNOSTIC ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debugFile = __DIR__ . '/debug_log.txt';
file_put_contents($debugFile, "=== " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
file_put_contents($debugFile, "1. Session user : " . print_r($_SESSION['user'] ?? 'vide', true) . "\n", FILE_APPEND);
file_put_contents($debugFile, "2. Panier : " . print_r($_SESSION['panier'] ?? 'vide', true) . "\n", FILE_APPEND);
// ====================================================

if (!isset($_SESSION['user'])) {
    header('Location: /facturation/auth/login.php');
    exit;
}

if (empty($_SESSION['panier'])) {
    header('Location: nouvelle-facture.php');
    exit;
}

// 1. Lire les produits
$produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
file_put_contents($debugFile, "3. Produits.json lu : " . print_r($produits, true) . "\n", FILE_APPEND);

$panier = $_SESSION['panier'];
$stockOK = true;
$messages = [];

// 2. Mettre à jour les stocks
foreach ($panier as $code => $item) {
    file_put_contents($debugFile, "4. Traitement code: $code, quantite: {$item['quantite']}\n", FILE_APPEND);
    
    $productFound = false;
    foreach ($produits as &$p) {
        if ($p['code_barre'] == $code) {  // Utiliser == au lieu de === pour éviter les problèmes de type
            $productFound = true;
            $stockAvant = (int)$p['quantite_stock'];
            $quantiteVendue = (int)$item['quantite'];
            
            file_put_contents($debugFile, "5. Produit trouvé: {$p['nom']}, stock avant: $stockAvant\n", FILE_APPEND);
            
            if ($stockAvant >= $quantiteVendue) {
                $p['quantite_stock'] = $stockAvant - $quantiteVendue;
                $messages[] = "✓ {$p['nom']} : $stockAvant → {$p['quantite_stock']}";
                file_put_contents($debugFile, "6. Nouveau stock: {$p['quantite_stock']}\n", FILE_APPEND);
            } else {
                $stockOK = false;
                $messages[] = "❌ Stock insuffisant pour {$p['nom']} (stock: $stockAvant)";
                file_put_contents($debugFile, "6. ERREUR: stock insuffisant\n", FILE_APPEND);
            }
            break;
        }
    }
    
    if (!$productFound) {
        file_put_contents($debugFile, "5. ERREUR: Produit $code non trouvé dans le catalogue\n", FILE_APPEND);
        $stockOK = false;
        $messages[] = "❌ Produit introuvable: $code";
    }
}

// 3. Vérifier si tout est OK
if (!$stockOK) {
    file_put_contents($debugFile, "7. STOCK_OK = FALSE, arrêt\n", FILE_APPEND);
    die("❌ Erreur :<br>" . implode("<br>", $messages));
}

file_put_contents($debugFile, "7. STOCK_OK = TRUE, sauvegarde...\n", FILE_APPEND);

// 4. Sauvegarder les produits mis à jour
$resultat = file_put_contents(PRODUCTS_FILE, json_encode($produits, JSON_PRETTY_PRINT));
file_put_contents($debugFile, "8. Écriture produits.json : " . ($resultat ? "RÉUSSIE ($resultat octets)" : "ÉCHEC") . "\n", FILE_APPEND);

// 5. Sauvegarder la facture
$factures = json_decode(file_get_contents(INVOICES_FILE), true);
$totalHT = 0;

foreach ($panier as $item) {
    $totalHT += $item['sous_total'];
}
$tva = round($totalHT * TVA_RATE, 2);
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
file_put_contents($debugFile, "9. Facture sauvegardée : $id\n", FILE_APPEND);

// 6. Vider le panier
$_SESSION['panier'] = [];
file_put_contents($debugFile, "10. Panier vidé\n", FILE_APPEND);
file_put_contents($debugFile, "=== FIN ===\n\n", FILE_APPEND);
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
    <div class="card">
        <h2>✅ Facture validée</h2>
        <p><strong>ID :</strong> <?= $id ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i:s') ?></p>
        <p><strong>Caissier :</strong> <?= $_SESSION['user']['identifiant'] ?></p>
        <p><strong>Total TTC :</strong> <?= number_format($ttc, 2) ?> CDF</p>

        <div style="margin: 1rem 0; padding: 0.5rem; background: #e8f5e9; border-radius: 8px;">
            <?php foreach ($messages as $msg): ?>
                <small><?= $msg ?></small><br>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 1rem;">
            <a href="afficher-facture.php?id=<?= urlencode($id) ?>" class="button">📄 Voir la facture</a>
            <a href="nouvelle-facture.php" class="button">🔄 Nouvelle facture</a>
            <a href="/facturation/index.php" class="button">🏠 Accueil</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>