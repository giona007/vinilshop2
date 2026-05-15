<?php
/*
 * chi-siamo.php
 * Pagina informativa statica che descrive lo scopo didattico del progetto.
 * Non effettua operazioni dinamiche importanti, mantiene il layout coerente
 * con il resto del sito tramite `renderHeader()` e `renderFooter()`.
 */
require __DIR__ . '/confing.php';
require __DIR__ . '/partials/template.php';
require __DIR__ . '/partials/header.php';
require __DIR__ . '/partials/footer.php';

if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Chi siamo'); ?>
</head>
<body>

<?php renderHeader(''); ?>

<main class="main-chi-siamo">
    <h1 class="titolo-sezione">Chi siamo</h1>

    <div class="chi-siamo-layout">
        <section class="chi-siamo-card">
            <h2 class="chi-siamo-card-title">Cos’è VinilShop</h2>
            <p>
                <strong>VinilShop</strong> è un sito web dimostrativo pensato per raccontare il mondo dei
                dischi in vinile con uno stile ispirato alla cultura hip hop e trap italiana.
                Non è un negozio vero: serve a mostrare come si organizza un piccolo e-commerce didattico
                con pagine collegate, contenuti curati e un’interfaccia leggibile su desktop e mobile.
            </p>
        </section>

        <section class="chi-siamo-card">
            <h2 class="chi-siamo-card-title">Cosa puoi fare sul sito</h2>
            <ul class="chi-siamo-list">
                <li><strong>Home</strong> — presentazione visiva e accesso rapido al catalogo.</li>
                <li><strong>Artisti</strong> — schede con link verso Spotify e Apple Music (solo collegamenti esterni).</li>
                <li><strong>Catalogo</strong> — vinili di esempio con modale per scegliere edizione e quantità.</li>
                <li><strong>Account e carrello</strong> — login, registrazione e carrello collegato alla sessione, per esercitarsi sul flusso utente.</li>
            </ul>
        </section>

        <section class="chi-siamo-card chi-siamo-card-note">
            <h2 class="chi-siamo-card-title">Nota per il visitatore</h2>
            <p>
                Tutti i prezzi, le immagini e i testi hanno valore esclusivamente formativo.
                Se noti un errore o vuoi proporre un miglioramento, puoi segnalarlo al docente o al gruppo che ha sviluppato il progetto.
            </p>
        </section>

        <p class="chi-siamo-back">
            <a href="index.php" class="link-indietro">← Torna alla home</a>
        </p>
    </div>
</main>

<?php renderFooter(); ?>

</body>
</html>
