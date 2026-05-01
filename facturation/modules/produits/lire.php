<?php
require_once __DIR__ . '/../../config/config.php';
session_start();

// Vérifier que l'utilisateur a les droits (Manager ou Super Admin)
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'manager' && $_SESSION['user']['role'] !== 'super_admin')) {
    echo json_encode(['erreur' => 'Accès non autorisé']);
    exit;
}

$codeBarre = $_GET['code_barre'] ?? '';

if (empty($codeBarre)) {
    echo json_encode(['erreur' => 'Code-barres manquant']);
    exit;
}

// Rechercher le produit
$produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
$trouve = null;

foreach ($produits as $produit) {
    if ($produit['code_barre'] === $codeBarre) {
        $trouve = $produit;
        break;
    }
}

if ($trouve) {
    echo json_encode([
        'trouve' => true,
        'produit' => $trouve
    ]);
} else {
    echo json_encode([
        'trouve' => false
    ]);
}
?>