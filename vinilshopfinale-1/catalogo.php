<?php
/*
 * catalogo.php
 * Esempio di catalogo con card statiche che simulano i vinili in vendita.
 * Le card contengono dati in attributi data-* che lo script.js legge per
 * popolare la modale di acquisto.
 */
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');

// gestione logout inline
if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: catalogo.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Catalogo'); ?>
</head>
<body>

<?php renderHeader('catalogo'); ?>

<main>

    <!-- FILTRO GENERE -->
    <div class="filtro">
        <label for="filtro-genere">Filtra per genere:</label>
        <select id="filtro-genere" onchange="filtraGenere(this.value)">
            <option value="tutti">tutti</option>
            <option value="trap">trap</option>
            <option value="rage">rage</option>
            <option value="rap">rap</option>
        </select>
    </div>

    <!-- GRIGLIA ALBUM -->
    <div class="griglia-album">

        <div class="card-album" data-vinile-id="1" data-genere="trap"
             data-titolo="Anti Anti" data-artista="18k"
             data-img="immagini/anti-anti.png"
             data-edizione-basic-id="1" data-basic="19.99"
             data-edizione-limited-id="2" data-limited="34.99"
             onclick="apriModale(this)">
            <img src="immagini/anti-anti.png" alt="Anti Anti">
            <p class="titolo-album">Anti Anti</p>
            <p class="artista-album">(18k)</p>
            <p class="genere-album">Genere: Trap</p>
        </div>

        <div class="card-album" data-vinile-id="2" data-genere="trap"
             data-titolo="IO" data-artista="18k"
             data-img="immagini/io.png"
             data-edizione-basic-id="3" data-basic="19.99"
             data-edizione-limited-id="4" data-limited="34.99"
             onclick="apriModale(this)">
            <img src="immagini/io.png" alt="IO">
            <p class="titolo-album">IO</p>
            <p class="artista-album">(18k)</p>
            <p class="genere-album">Genere: Trap</p>
        </div>

        <div class="card-album" data-vinile-id="3" data-genere="rage"
             data-titolo="Crash Out" data-artista="Aira"
             data-img="immagini/crashout.png"
             data-edizione-basic-id="5" data-basic="19.99"
             data-edizione-limited-id="6" data-limited="34.99"
             onclick="apriModale(this)">
            <img src="immagini/crashout.png" alt="Crash Out">
            <p class="titolo-album">CRASH OUT</p>
            <p class="artista-album">(Aira)</p>
            <p class="genere-album">Genere: Rage</p>
        </div>

        <div class="card-album" data-vinile-id="4" data-genere="rap"
             data-titolo="Anche gli eroi muoiono" data-artista="Kid Yugi"
             data-img="immagini/anche-gli-eroi-muoiono.png"
             data-edizione-basic-id="7" data-basic="19.99"
             data-edizione-limited-id="8" data-limited="34.99"
             onclick="apriModale(this)">
            <img src="immagini/anche-gli-eroi-muoiono.png" alt="Anche gli eroi muoiono">
            <p class="titolo-album">Anche gli eroi muoiono</p>
            <p class="artista-album">(Kid Yugi)</p>
            <p class="genere-album">Genere: Rap</p>
        </div>

        <div class="card-album" data-vinile-id="5" data-genere="trap"
             data-titolo="The Globe" data-artista="Kid Yugi"
             data-img="immagini/the-globe-baisc.png"
             data-edizione-basic-id="9" data-basic="19.99"
             data-edizione-limited-id="10" data-limited="34.99"
             onclick="apriModale(this)">
            <img src="immagini/the-globe-baisc.png" alt="The Globe">
            <p class="titolo-album">The Globe</p>
            <p class="artista-album">(Kid Yugi)</p>
            <p class="genere-album">Genere: Trap</p>
        </div>

        <div class="card-album" data-vinile-id="6" data-genere="rap"
             data-titolo="Morendo ad occhi aperti" data-artista="Promessa"
             data-img="immagini/morendo-ad-occhi-aperti.png"
             data-edizione-basic-id="11" data-basic="19.99"
             data-edizione-limited-id="12" data-limited="34.99"
             onclick="apriModale(this)">
            <img src="immagini/morendo-ad-occhi-aperti.png" alt="Morendo ad occhi aperti">
            <p class="titolo-album">Morendo ad occhi aperti</p>
            <p class="artista-album">(Promessa)</p>
            <p class="genere-album">Genere: Rap</p>
        </div>

    </div>
</main>

<!-- MODALE ACQUISTO -->
<div id="modale-acquisto" class="modale-overlay" style="display:none"
     onclick="if(event.target===this) chiudiModale()">
    <div class="modale-box">
        <button class="modale-chiudi" onclick="chiudiModale()">✕</button>
        <img id="m-img" src="" alt="">
        <h2 id="m-titolo"></h2>
        <p id="m-artista"></p>

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
        <div class="controllo-quantita-modale">
            <label for="m-quantita">Quanti ne ordini:</label>
            <input id="m-quantita" type="number" min="1" value="1">
        </div>

        <?php if (isset($_SESSION['utente_id'])): ?>
            <button class="btn-aggiungi-carrello" onclick="aggiungiAlCarrello()">Aggiungi al carrello 🛒</button>
        <?php else: ?>
            <p class="msg-login-required">⚠️ <a href="login.php">Accedi</a> per aggiungere al carrello.</p>
        <?php endif; ?>
    </div>
</div>

<!-- TOAST AGGIUNTO AL CARRELLO -->
<div id="popup-aggiunto" class="popup-toast" style="display:none">
    <span class="toast-msg">Prodotto aggiunto al carrello.</span>
</div>

    <?php renderFooter(); ?>

<script src="script.js"></script>

</body>
</html>
