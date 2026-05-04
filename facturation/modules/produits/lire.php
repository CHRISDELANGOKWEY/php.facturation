<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['erreur' => 'Non connecté']);
    exit;
}

$codeBarre = $_GET['code_barre'] ?? '';
$produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
$trouve = null;

foreach ($produits as $p) {
    if ($p['code_barre'] === $codeBarre) {
        $trouve = $p;
        break;
    }
}

if ($trouve) {
    echo json_encode(['trouve' => true, 'produit' => $trouve]);
} else {
    echo json_encode(['trouve' => false]);
}