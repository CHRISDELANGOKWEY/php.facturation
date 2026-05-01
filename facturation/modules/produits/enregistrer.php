<?php
require_once __DIR__ . '/../../auth/session.php';

// Vérifier le rôle (Manager ou Super Admin)
if (!aRole('manager')) {
    die('Accès non autorisé. Vous devez être Manager ou Super Administrateur.');
}

$message = '';
$codeBarre = '';

// Traitement du formulaire d'enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enregistrer') {
    $codeBarre = $_POST['code_barre'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prix = floatval($_POST['prix_unitaire_ht'] ?? 0);
    $dateExpiration = $_POST['date_expiration'] ?? '';
    $quantite = intval($_POST['quantite_stock'] ?? 0);
    
    // Validations
    $erreurs = [];
    
    if (empty($nom)) {
        $erreurs[] = 'Le nom du produit est obligatoire';
    }
    if ($prix <= 0) {
        $erreurs[] = 'Le prix doit être supérieur à 0';
    }
    if ($quantite < 0) {
        $erreurs[] = 'La quantité ne peut pas être négative';
    }
    if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateExpiration)) {
        $erreurs[] = 'Format de date invalide (MM-JJ-AAAA)';
    } else {
        // Vérifier que la date est valide
        $dateParts = explode('-', $dateExpiration);
        if (!checkdate($dateParts[0], $dateParts[1], $dateParts[2])) {
            $erreurs[] = 'Date d\'expiration invalide';
        }
    }
    
    if (empty($erreurs)) {
        // Vérifier si le code-barres existe déjà
        $produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
        $existe = false;
        foreach ($produits as $p) {
            if ($p['code_barre'] === $codeBarre) {
                $existe = true;
                break;
            }
        }
        
        if (!$existe) {
            // Créer le nouveau produit
            $nouveauProduit = [
                'code_barre' => $codeBarre,
                'nom' => $nom,
                'prix_unitaire_ht' => $prix,
                'date_expiration' => $dateExpiration,
                'quantite_stock' => $quantite,
                'date_enregistrement' => date('Y-m-d')
            ];
            
            $produits[] = $nouveauProduit;
            file_put_contents(PRODUCTS_FILE, json_encode($produits, JSON_PRETTY_PRINT));
            $message = '<p class="success">Produit enregistré avec succès !</p>';
            
            // Réinitialiser le formulaire
            $codeBarre = '';
        } else {
            $message = '<p class="error">Ce code-barres existe déjà !</p>';
        }
    } else {
        $message = '<div class="error"><ul>';
        foreach ($erreurs as $err) {
            $message .= '<li>' . htmlspecialchars($err) . '</li>';
        }
        $message .= '</ul></div>';
    }
}

// Récupérer le code-barres passé en GET (pour pré-remplir)
if (empty($codeBarre) && isset($_GET['code_barre'])) {
    $codeBarre = htmlspecialchars($_GET['code_barre']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enregistrement produit</title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
    <script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
    <script src="/facturation/assets/js/scanner.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <main>
        <h1>Enregistrement des produits</h1>
        
        <?php echo $message; ?>
        
        <div class="scan-section">
            <h2>Scanner un code-barres</h2>
            <button id="btn-scan" onclick="lancerScan()">Lancer la caméra</button>
            <button id="btn-arreter" onclick="arreterScan()" style="display:none">Arrêter la caméra</button>
            
            <div id="scan-preview" style="display:none; margin-top: 20px;">
                <video id="video-preview" width="100%" style="max-width: 500px; border: 1px solid #ccc;"></video>
            </div>
            <div id="scan-status"></div>
            <div id="produit-info"></div>
        </div>
        
        <div id="formulaire-produit" style="display: none;">
            <h2>Formulaire d'enregistrement</h2>
            <form method="post">
                <input type="hidden" name="action" value="enregistrer">
                <input type="hidden" name="code_barre" id="code-barre-recupere" value="">
                
                <label>Nom du produit :</label>
                <input type="text" name="nom" required>
                
                <label>Prix unitaire HT (CDF) :</label>
                <input type="number" name="prix_unitaire_ht" step="0.01" min="0.01" required>
                
                <label>Date d'expiration (MM-JJ-AAAA) :</label>
                <input type="text" name="date_expiration" placeholder="04-30-2026" required>
                
                <label>Quantité initiale en stock :</label>
                <input type="number" name="quantite_stock" min="0" value="0" required>
                
                <button type="submit">Enregistrer le produit</button>
            </form>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>