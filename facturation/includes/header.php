<header>
    <div class="header-content">
        <h1>CHATEAUX FACTURE</h1>
        <div class="user-info">
            <?php if (isset($_SESSION['user'])): ?>
                <span><?php echo htmlspecialchars($_SESSION['user']['nom_complet']); ?></span>
                <a href="/facturation/auth/logout.php">Déconnexion</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<nav>
    <a href="/facturation/index.php">Accueil</a>
    <a href="/facturation/modules/facturation/nouvelle-facture.php">Facturer</a>
</nav>