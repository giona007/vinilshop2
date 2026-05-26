<?php
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');
 
// Gestisce il logout inviato dal form nell'header
if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
 
/*
 * Genera la chiave univoca di un articolo nel carrello.
 * Formato: "vinileId_edizioneId"  (es. "3_6")
 * Deve coincidere con makeChiave() in carrello_action.php.
 */
function makeChiave(int $vId, int $eId): string
{
    return $vId . '_' . $eId;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Carrello'); ?>
</head>
<body>
 
<?php renderHeader(); ?>
 
<main>
    <h2 class="titolo-sezione">Il tuo carrello</h2>
 
    <?php if (!isset($_SESSION['utente_id'])): ?>
        <!-- Utente non loggato -->
        <p class="carrello-vuoto">
            Devi <a href="login.php">accedere</a> per visualizzare il carrello.
        </p>
 
    <?php elseif (empty($_SESSION['carrello'])): ?>
        <!-- Carrello vuoto -->
        <p class="carrello-vuoto">
            Il carrello è vuoto. <a href="catalogo.php">Vai al catalogo →</a>
        </p>
 
    <?php else:
        $totalePrezzo = 0;
 
        foreach ($_SESSION['carrello'] as $chiaveRaw => $item):
            // Ricalcola la chiave "pulita"; usa il fallback se mancano gli ID
            $vId    = (int) ($item['vinile_id']   ?? 0);
            $eId    = (int) ($item['edizione_id'] ?? 0);
            $chiave = ($vId > 0 && $eId > 0) ? makeChiave($vId, $eId) : $chiaveRaw;
 
            $subtotale     = (float) $item['prezzo'] * (int) $item['quantita'];
            $totalePrezzo += $subtotale;
 
            // Converte il codice edizione in etichetta leggibile
            $labelEd = match ((string) ($item['edizione'] ?? '')) {
                'basic'   => 'Basic Edition',
                'limited' => 'Limited Edition',
                default   => ucfirst($item['edizione'] ?? '') . ' Edition',
            };
 
            // Chiave sicura per gli attributi id="riga-..." e id="q-..."
            $idSafe = htmlspecialchars($chiave, ENT_QUOTES, 'UTF-8');
    ?>
        <!-- Riga prodotto -->
        <div class="riga-carrello" id="riga-<?= $idSafe ?>">
            <img src="<?= htmlspecialchars($item['img']) ?>"
                 alt="<?= htmlspecialchars($item['titolo']) ?>">
 
            <div class="riga-info">
                <p class="riga-titolo"><?= htmlspecialchars($item['titolo']) ?></p>
                <p class="riga-artista"><?= htmlspecialchars($item['artista']) ?></p>
                <p class="riga-edizione"><?= htmlspecialchars($labelEd) ?></p>
                <p class="riga-qta-text">
                    Quantità: <strong><?= (int) $item['quantita'] ?></strong>
                </p>
                <p class="riga-prezzo">
                    € <?= number_format((float) $item['prezzo'], 2, '.', '') ?> cad.
                </p>
            </div>
 
            <!-- Controlli quantità (−, numero, +) -->
            <div class="riga-quantita">
                <button type="button"
                    onclick='cambiaQ("diminuisci", <?= json_encode($chiave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>−</button>
                <span id="q-<?= $idSafe ?>"><?= (int) $item['quantita'] ?></span>
                <button type="button"
                    onclick='cambiaQ("aumenta", <?= json_encode($chiave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>+</button>
            </div>
 
            <!-- Subtotale della riga -->
            <div class="riga-subtotale">
                € <span id="sub-<?= $idSafe ?>"><?= number_format($subtotale, 2, '.', '') ?></span>
            </div>
 
            <!-- Pulsante rimozione -->
            <button type="button" class="btn-rimuovi"
                onclick='cambiaQ("rimuovi", <?= json_encode($chiave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>🗑</button>
        </div>
 
    <?php endforeach; ?>
 
        <!-- Totale e pulsante acquisto -->
        <div class="carrello-footer">
            <p class="totale-carrello">
                Totale: €&nbsp;<span id="totale-generale"><?= number_format($totalePrezzo, 2, '.', '') ?></span>
            </p>
            <button class="btn-acquista" onclick="effettuaOrdine()">Acquista</button>
        </div>
 
        <!--
            Oggetto JS con prezzo e quantità di ogni articolo.
            Il JS lo usa per aggiornare subtotali e totale senza ricaricare la pagina.
        -->
        <script>
        const prezziItem = {
        <?php foreach ($_SESSION['carrello'] as $chiaveRaw => $item):
            $vId2    = (int) ($item['vinile_id']   ?? 0);
            $eId2    = (int) ($item['edizione_id'] ?? 0);
            $chiave2 = ($vId2 > 0 && $eId2 > 0) ? makeChiave($vId2, $eId2) : $chiaveRaw;
        ?>
            <?= json_encode((string) $chiave2, JSON_HEX_TAG | JSON_HEX_APOS) ?>:
                { prezzo: <?= (float) $item['prezzo'] ?>, quantita: <?= (int) $item['quantita'] ?> },
        <?php endforeach; ?>
        };
        </script>
 
    <?php endif; ?>
</main>
 
<!-- Popup di conferma ordine effettuato -->
<div id="popup-ordine" class="popup-ordine-overlay" style="display:none">
    <div class="popup-ordine-box">
        <div class="popup-checkmark">✔</div>
        <h2>Ordine effettuato!</h2>
        <p>Grazie per il tuo acquisto
            <?= isset($_SESSION['utente_nome'])
                ? ', ' . htmlspecialchars($_SESSION['utente_nome'])
                : '' ?>!
        </p>
        <button onclick="chiudiOrdine()">Continua a fare shopping</button>
    </div>
</div>
 
<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>
