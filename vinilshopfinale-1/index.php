<?php  
/*
 * index.php
 * Home page semplice che mostra immagini di esempio e un link rapido
 * al catalogo. Usa le funzioni dei partials per head, header e footer.
 */
include ('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');

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

    <div class="artisti">
        <img src="immagini/faneto.png" alt="Faneto" class="artista">
        <img src="immagini/artie.png" alt="Artie" class="artista">
        <img src="immagini/shiva.png" alt="Shiva" class="artista">
    </div>

    <div class="scopri">
        <a href="catalogo.php" class="btn-scopri">Scopri di più</a>
    </div>

    <?php renderFooter(); ?>

</body>
</html>