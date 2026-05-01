<?php
function genererIdFacture() {
    $date = date('Ymd');
    $factures = json_decode(file_get_contents(INVOICES_FILE), true);
    
    $compteur = 1;
    foreach ($factures as $facture) {
        if (strpos($facture['id_facture'], "FAC-{$date}-") === 0) {
            $num = intval(substr($facture['id_facture'], -3));
            if ($num >= $compteur) {
                $compteur = $num + 1;
            }
        }
    }
    
    return sprintf("FAC-%s-%03d", $date, $compteur);
}

function calculerTVA($montantHT) {
    return round($montantHT * TVA_RATE, 2);
}

function calculerTTC($montantHT) {
    return round($montantHT + calculerTVA($montantHT), 2);
}

function mettreAJourStock($codeBarre, $quantiteVendue) {
    $produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
    
    foreach ($produits as &$produit) {
        if ($produit['code_barre'] === $codeBarre) {
            if ($produit['quantite_stock'] >= $quantiteVendue) {
                $produit['quantite_stock'] -= $quantiteVendue;
                file_put_contents(PRODUCTS_FILE, json_encode($produits, JSON_PRETTY_PRINT));
                return true;
            }
            return false;
        }
    }
    return false;
}

function verifierStock($codeBarre, $quantiteDemandee) {
    $produits = json_decode(file_get_contents(PRODUCTS_FILE), true);
    
    foreach ($produits as $produit) {
        if ($produit['code_barre'] === $codeBarre) {
            return $produit['quantite_stock'] >= $quantiteDemandee;
        }
    }
    return false;
}

function sauvegarderFacture($panier, $totalHT) {
    $factures = json_decode(file_get_contents(INVOICES_FILE), true);
    
    $idFacture = genererIdFacture();
    $tva = calculerTVA($totalHT);
    $totalTTC = calculerTTC($totalHT);
    
    $articles = [];
    foreach ($panier as $code => $item) {
        $articles[] = [
            'code_barre' => $code,
            'nom' => $item['nom'],
            'prix_unitaire_ht' => $item['prix'],
            'quantite' => $item['quantite'],
            'sous_total_ht' => $item['sous_total']
        ];
    }
    
    $facture = [
        'id_facture' => $idFacture,
        'date' => date('Y-m-d'),
        'heure' => date('H:i:s'),
        'caissier' => $_SESSION['user']['identifiant'],
        'articles' => $articles,
        'total_ht' => $totalHT,
        'tva' => $tva,
        'total_ttc' => $totalTTC
    ];
    
    $factures[] = $facture;
    file_put_contents(INVOICES_FILE, json_encode($factures, JSON_PRETTY_PRINT));
    
    return $idFacture;
}
?>