<?php
/*
 * index.php
 * Home page: mostra tre immagini di artisti e un pulsante
 * che porta al catalogo.
 */
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');
 
// Gestisce il logout inviato dal form nell'header
if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Home'); ?>
</head>
<body>
 
    <?php renderHeader('home'); ?>
 
    <!-- Tre immagini degli artisti in evidenza -->
    <div class="artisti">
        <img src="immagini/faneto.png" alt="Faneto" class="artista">
        <img src="immagini/artie.png"  alt="Artie"  class="artista">
        <img src="immagini/shiva.png"  alt="Shiva"  class="artista">
    </div>
 
    <!-- Pulsante per andare al catalogo -->
    <div class="scopri">
        <a href="catalogo.php" class="btn-scopri">Scopri di più</a>
    </div>
 
    <?php renderFooter(); ?>
</body>
</html>
