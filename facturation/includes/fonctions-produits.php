<?php
function getAllProducts() {
    return json_decode(file_get_contents(PRODUCTS_FILE), true);
}

function findProductByCode($codeBarre) {
    $produits = getAllProducts();
    foreach ($produits as $p) {
        if ($p['code_barre'] === $codeBarre) {
            return $p;
        }
    }
    return null;
}

function updateStock($codeBarre, $nouvelleQuantite) {
    $produits = getAllProducts();
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $codeBarre) {
            $p['quantite_stock'] = (int)$nouvelleQuantite;
            file_put_contents(PRODUCTS_FILE, json_encode($produits, JSON_PRETTY_PRINT));
            return true;
        }
    }
    return false;
}
?>