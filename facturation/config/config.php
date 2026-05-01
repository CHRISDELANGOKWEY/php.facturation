<?php
// Taux de TVA (18% = 0.18)
define('TVA_RATE', 0.18);

// Chemins absolus des fichiers de données
define('DATA_PATH', __DIR__ . '/../data/');
define('PRODUCTS_FILE', DATA_PATH . 'produits.json');
define('INVOICES_FILE', DATA_PATH . 'factures.json');
define('USERS_FILE', DATA_PATH . 'utilisateurs.json');

// Créer le dossier data s'il n'existe pas
if (!file_exists(DATA_PATH)) {
    mkdir(DATA_PATH, 0777, true);
}

// Initialiser les fichiers JSON s'ils n'existent pas
function initDataFiles() {
    $files = [PRODUCTS_FILE, INVOICES_FILE, USERS_FILE];
    foreach ($files as $file) {
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }
    }
}
initDataFiles();
?>