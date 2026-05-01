 Système de caisse - PHP procédural

 Installation
1. Copier le dossier `facturation` dans `C:\laragon\www\`
2. Démarrer Laragon
3. Accéder à `http://localhost/facturation/`

  Compte par défaut
 Identifiant:super admin ;  Mot de passe : mot de passe en anglais;    Rôle: super Administrateur
   

  Fonctionnalités
- Scanner code-barres (ZXing)
- Enregistrement produit
- Facturation (TVA 18%)
- Mise à jour automatique du stock
- Gestion des comptes (Caissier/Manager/SuperAdmin)
- Rapports journalier et mensuel
  Technologies
- PHP 8 (procédural)
- JSON (pas de base de données)
- HTML/CSS
- JavaScript (ZXing)

 Structure des données
data/
 produits.json
 factures.json
 utilisateurs.json
