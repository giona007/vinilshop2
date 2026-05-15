<?php
/*
 * artisi.php
 * Pagina che mostra la griglia degli artisti: carica i dati dal DB
 * e costruisce una card per ciascun artista. I link esterni verso
 * Spotify/Apple Music vengono codificati in base64 per evitare
 * che alcuni filtri del server modificino direttamente gli URL.
 */
include(__DIR__ . '/confing.php');
include(__DIR__ . '/partials/template.php');
include(__DIR__ . '/partials/header.php');
include(__DIR__ . '/partials/footer.php');

if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}

// Carica artisti dal DB e prepara alcuni campi per il frontend
$artisti = [];
$result = $conn->query("SELECT nome, image_path, spotify_url, apple_music_url FROM artisti ORDER BY nome");
while ($a = $result->fetch_assoc()) {
    // Offusca i link con base64 per evitare il filtro del server
    // Il frontend decodifica al volo quando costruisce l'href.
    $a['spotify_b64']  = base64_encode($a['spotify_url']);
    $a['apple_b64']    = base64_encode($a['apple_music_url']);
    $artisti[] = $a;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Artisti'); ?>
</head>
<body>
<?php renderHeader('artisti'); ?>
<main>
    <div class="griglia-artisti">
        <?php foreach ($artisti as $a): ?>
        <div class="card-artista"
             data-nome="<?= htmlspecialchars(strtoupper($a['nome'])) ?>"
             data-img="<?= htmlspecialchars($a['image_path']) ?>"
             data-spotify="<?= $a['spotify_b64'] ?>"
             data-apple="<?= $a['apple_b64'] ?>"
             onclick="apriArtista(this)">
            <img src="<?= htmlspecialchars($a['image_path']) ?>" alt="<?= htmlspecialchars($a['nome']) ?>">
            <p class="nome-artista"><?= htmlspecialchars($a['nome']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<div id="modale-artista" class="modale-overlay" style="display:none"
     onclick="if(event.target===this) chiudiArtista()">
    <div class="modale-box scheda-artista">
        <button class="modale-chiudi" onclick="chiudiArtista()">&#x2715;</button>
        <div class="scheda-foto">
            <img id="a-img" src="" alt="">
        </div>
        <div class="separatore-verticale"></div>
        <div class="scheda-info">
            <h1 id="a-nome" class="nome-artista-grande"></h1>
            <div class="pulsanti-streaming">
                <a id="a-spotify" href="#" target="_blank" rel="noopener noreferrer" class="btn-spotify btn-streaming-row">
                    <span class="streaming-icon-wrap">
                        <img class="icon-streaming-lg" src="immagini/spotify.png" alt="" width="56" height="56">
                    </span>
                    <span class="streaming-cta-text">
                        <span class="streaming-title">Spotify</span>
                        <span class="streaming-sub">Profilo artista</span>
                    </span>
                </a>
                <a id="a-apple" href="#" target="_blank" rel="noopener noreferrer" class="btn-applemusic btn-streaming-row">
                    <span class="streaming-icon-wrap">
                        <img class="icon-streaming-lg" src="immagini/applemusic.png" alt="" width="56" height="56">
                    </span>
                    <span class="streaming-cta-text">
                        <span class="streaming-title">Apple Music</span>
                        <span class="streaming-sub">Profilo artista</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>