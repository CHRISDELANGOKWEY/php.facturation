<?php
require_once __DIR__ . '/../../auth/session.php';

if (!aRole('super_admin')) die("Accès refusé.");

$identifiant = $_GET['identifiant'] ?? '';

if ($identifiant === $_SESSION['user']['identifiant']) {
    die("❌ Vous ne pouvez pas supprimer votre propre compte.");
}

$users = json_decode(file_get_contents(USERS_FILE), true);
$newUsers = [];

foreach ($users as $u) {
    if ($u['identifiant'] !== $identifiant) {
        $newUsers[] = $u;
    }
}

file_put_contents(USERS_FILE, json_encode($newUsers, JSON_PRETTY_PRINT));
header('Location: gestion-comptes.php');
exit;