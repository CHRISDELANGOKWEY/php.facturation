<?php
function estConnecte() {
    return isset($_SESSION['user']);
}

function aRole($roleRequis) {
    if (!estConnecte()) return false;
    $role = $_SESSION['user']['role'];
    return $role === $roleRequis || $role === 'super_admin';
}

function getUtilisateurConnecte() {
    return $_SESSION['user'] ?? null;
}
?>