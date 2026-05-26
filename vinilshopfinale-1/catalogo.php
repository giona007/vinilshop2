<?php
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');
 
// Gestisce il logout inviato dal form nell'header
if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: catalogo.php");
    exit;
}
 
/*
 * Elenco degli album statici.
 * Ogni voce contiene:
 *   vinileId, genere, titolo, artista, img,
 *   edizioneBasicId, basicPrice, edizioneLimitedId, limitedPrice
 *
 * Gli ID edizione corrispondono alle righe nella tabella `vinili_edizioni`.
 */
$albums = [
    [1, 'trap', 'Anti Anti',                   '18k',       'immagini/anti-anti.png',                  1, '19.99',  2, '34.99'],
    [2, 'trap', 'IO',                           '18k',       'immagini/io.png',                         3, '19.99',  4, '34.99'],
    [3, 'rage', 'Crash Out',                    'Aira',      'immagini/crashout.png',                   5, '19.99',  6, '34.99'],
    [4, 'rap',  'Anche gli eroi muoiono',       'Kid Yugi',  'immagini/anche-gli-eroi-muoiono.png',     7, '19.99',  8, '34.99'],
    [5, 'trap', 'The Globe',                    'Kid Yugi',  'immagini/the-globe-baisc.png',            9, '19.99', 10, '34.99'],
    [6, 'rap',  'Morendo ad occhi aperti',      'Promessa',  'immagini/morendo-ad-occhi-aperti.png',   11, '19.99', 12, '34.99'],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Catalogo'); ?>
</head>
<body>
 
<?php renderHeader('catalogo'); ?>
 
<main>
    <!-- Filtro per genere musicale -->
    <div class="filtro">
        <label for="filtro-genere">Filtra per genere:</label>
        <select id="filtro-genere" onchange="filtraGenere(this.value)">
            <option value="tutti">Tutti</option>
            <option value="trap">Trap</option>
            <option value="rage">Rage</option>
            <option value="rap">Rap</option>
        </select>
    </div>
 
    <!-- Griglia album: ogni card porta i dati dell'album negli attributi data-* -->
    <div class="griglia-album">
        <?php foreach ($albums as [$vid, $genere, $titolo, $artista, $img, $basicId, $basic, $limitedId, $limited]): ?>
        <div class="card-album"
             data-vinile-id="<?= $vid ?>"
             data-genere="<?= $genere ?>"
             data-titolo="<?= htmlspecialchars($titolo) ?>"
             data-artista="<?= htmlspecialchars($artista) ?>"
             data-img="<?= htmlspecialchars($img) ?>"
             data-edizione-basic-id="<?= $basicId ?>"   data-basic="<?= $basic ?>"
             data-edizione-limited-id="<?= $limitedId ?>" data-limited="<?= $limited ?>"
             onclick="apriModale(this)">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($titolo) ?>">
            <p class="titolo-album"><?= htmlspecialchars($titolo) ?></p>
            <p class="artista-album">(<?= htmlspecialchars($artista) ?>)</p>
            <p class="genere-album">Genere: <?= ucfirst($genere) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</main>
 
<!-- Modale di acquisto: si apre al click su una card -->
<div id="modale-acquisto" class="modale-overlay" style="display:none"
     onclick="if(event.target===this) chiudiModale()">
    <div class="modale-box">
        <button class="modale-chiudi" onclick="chiudiModale()">✕</button>
        <img id="m-img" src="" alt="">
        <h2 id="m-titolo"></h2>
        <p  id="m-artista"></p>
 
        <!-- Scelta edizione: Basic o Limited -->
        <div class="modale-edizioni">
            <label class="edizione-radio">
                <input type="radio" name="edizione" value="basic" checked onchange="aggiornaPrezzo()">
                <span>Basic Edition — € <span id="m-prezzo-basic"></span></span>
            </label>
            <label class="edizione-radio">
                <input type="radio" name="edizione" value="limited" onchange="aggiornaPrezzo()">
                <span>Limited Edition — € <span id="m-prezzo-limited"></span></span>
            </label>
        </div>
 
        <p class="prezzo-selezionato">Prezzo: <strong>€ <span id="m-prezzo-display"></span></strong></p>
 
        <!-- Selettore quantità -->
        <div class="controllo-quantita-modale">
            <label for="m-quantita">Quanti ne ordini:</label>
            <input id="m-quantita" type="number" min="1" value="1">
        </div>
 
        <?php if (isset($_SESSION['utente_id'])): ?>
            <button class="btn-aggiungi-carrello" onclick="aggiungiAlCarrello()">Aggiungi al carrello 🛒</button>
        <?php else: ?>
            <!-- Avvisa l'utente non loggato che deve prima accedere -->
            <p class="msg-login-required">⚠️ <a href="login.php">Accedi</a> per aggiungere al carrello.</p>
        <?php endif; ?>
    </div>
</div>
 
<!-- Toast temporaneo "prodotto aggiunto" -->
<div id="popup-aggiunto" class="popup-toast" style="display:none">
    <span class="toast-msg">Prodotto aggiunto al carrello.</span>
</div>
 
<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>
