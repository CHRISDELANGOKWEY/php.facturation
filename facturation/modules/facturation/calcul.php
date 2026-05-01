<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: /facturation/auth/login.php');
    exit;
}

$response = ['totalHT' => 0, 'tva' => 0, 'ttc' => 0];

if (isset($_SESSION['panier'])) {
    $totalHT = 0;
    foreach ($_SESSION['panier'] as $item) {
        $totalHT += $item['sous_total'];
    }
    $response['totalHT'] = $totalHT;
    $response['tva'] = round($totalHT * TVA_RATE, 2);
    $response['ttc'] = $totalHT + $response['tva'];
}

header('Content-Type: application/json');
echo json_encode($response);