<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/fonctions-auth.php';

session_start();

if (!estConnecte()) {
    header('Location: /facturation/auth/login.php');
    exit;
}
?>