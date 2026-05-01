<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: /facturation/auth/login.php');
    exit;
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';
$totalHT = 0;

// Ajout produit au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code_barre'])) {
    $code = $_POST['code_barre'];
    $qte = (int)$_POST['quantite'];

    $produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
    $trouve = null;
    foreach ($produits as $p) {
        if ($p['code_barre'] === $code) {
            $trouve = $p;
            break;
        }
    }

    if ($trouve) {
        // ✅ CORRECTION ICI : conversion en int
        if ((int)$trouve['quantite_stock'] >= (int)$qte) {
            if (isset($_SESSION['panier'][$code])) {
                $_SESSION['panier'][$code]['quantite'] += $qte;
                $_SESSION['panier'][$code]['sous_total'] = $_SESSION['panier'][$code]['quantite'] * $trouve['prix_unitaire_ht'];
            } else {
                $_SESSION['panier'][$code] = [
                    'nom' => $trouve['nom'],
                    'prix' => $trouve['prix_unitaire_ht'],
                    'quantite' => $qte,
                    'sous_total' => $qte * $trouve['prix_unitaire_ht']
                ];
            }
            $message = "✅ Produit ajouté";
        } else {
            $message = "❌ Stock insuffisant (stock actuel : " . (int)$trouve['quantite_stock'] . ")";
        }
    } else {
        $message = "❌ Produit inconnu. Enregistrez-le d'abord.";
    }
}

// Supprimer un article
if (isset($_GET['supprimer'])) {
    unset($_SESSION['panier'][$_GET['supprimer']]);
    header('Location: nouvelle-facture.php');
    exit;
}

// Vider panier
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header('Location: nouvelle-facture.php');
    exit;
}

// Calcul total
foreach ($_SESSION['panier'] as $item) {
    $totalHT += $item['sous_total'];
}
$tva = round($totalHT * 0.18, 2);
$ttc = $totalHT + $tva;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nouvelle facture</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
    <script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
    <script src="/facturation/assets/js/scanner-facture.js"></script>
</head>
<body>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<main>
    <h2>🛒 Nouvelle facture</h2>

    <?php if ($message): ?>
        <p style="color:green"><?= $message ?></p>
    <?php endif; ?>

    <h3>📷 Scanner un produit</h3>
    <button type="button" id="btn-scan" onclick="lancerScan()">Lancer la caméra</button>
    <button type="button" id="btn-arreter" onclick="arreterScan()" style="display:none">Arrêter</button>
    <div id="scan-preview" style="display:none; margin-top:10px;">
        <video id="video-preview" width="100%" style="max-width:400px;"></video>
    </div>
    <div id="scan-status"></div>

    <div id="ajout-quantite" style="display:none; margin-top:20px; border:1px solid #ccc; padding:10px;">
        <h3>➕ Ajouter au panier</h3>
        <input type="hidden" id="code-barres-ajout">
        <label>Quantité :</label>
        <input type="number" id="quantite-ajout" value="1" min="1">
        <button onclick="ajouterAuPanier()">Ajouter</button>
        <button onclick="annulerAjout()">Annuler</button>
    </div>

    <hr>

    <h3>📦 Panier actuel</h3>
    <?php if (empty($_SESSION['panier'])): ?>
        <p>Aucun produit dans le panier.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Produit</th><th>Prix HT</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($_SESSION['panier'] as $code => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nom']) ?></td>
                <td><?= number_format($item['prix'], 2) ?> CDF</td>
                <td><?= $item['quantite'] ?></td>
                <td><?= number_format($item['sous_total'], 2) ?> CDF</td>
                <td><a href="?supprimer=<?= urlencode($code) ?>">❌</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Total HT :</strong> <?= number_format($totalHT, 2) ?> CDF</p>
        <p><strong>TVA (18%) :</strong> <?= number_format($tva, 2) ?> CDF</p>
        <p><strong>Net à payer :</strong> <?= number_format($ttc, 2) ?> CDF</p>

        <form method="post" action="valider-facture.php">
            <button type="submit">✅ Valider la facture</button>
            <a href="?vider=1" onclick="return confirm('Vider le panier ?')">🗑️ Vider</a>
        </form>
    <?php endif; ?>

    <br>
    <a href="/facturation/modules/produits/enregistrer.php">📦 Enregistrer un nouveau produit</a><br>
    <a href="/facturation/index.php">🏠 Retour à l'accueil</a>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>