<?php
/*
 * carrello.php
 * Pagina che visualizza il contenuto del carrello memorizzato in sessione.
 * Mostra le righe, permette di modificare quantità e avviare il checkout.
 */
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');

if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

/**
 * Ricrea la chiave usata in carrello_action.php:
 * vinileId_edizioneId  (underscore, senza caratteri speciali)
 *
 * La funzione è usata sia per generare ID coerenti per gli elementi HTML
 * sia per mantenere lo stesso formato tra frontend e backend.
 */
function makeChiave(int $vinileId, int $edizioneId): string
{
    return $vinileId . '_' . $edizioneId;
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

        <p class="carrello-vuoto">
            Devi <a href="login.php">accedere</a> per visualizzare il carrello.
        </p>

    <?php elseif (empty($_SESSION['carrello'])): ?>

        <p class="carrello-vuoto">Il carrello è vuoto. <a href="catalogo.php">Vai al catalogo →</a></p>

    <?php else:
        $totalePrezzo = 0;
        foreach ($_SESSION['carrello'] as $chiaveRaw => $item):
            /*
             * FIX: la chiave viene costruita con makeChiave() sia qui che in
             * carrello_action.php, così sono sempre allineati.
             * Se vinile_id e edizione_id esistono, ricalcoliamo la chiave
             * "pulita"; altrimenti usiamo quella già presente (fallback vecchio schema).
             */
            $vId    = (int) ($item['vinile_id']   ?? 0);
            $eId    = (int) ($item['edizione_id'] ?? 0);
            $chiave = ($vId > 0 && $eId > 0) ? makeChiave($vId, $eId) : $chiaveRaw;

            $subtotale    = (float) $item['prezzo'] * (int) $item['quantita'];
            $totalePrezzo += $subtotale;

            // Etichetta edizione leggibile
            $codiceEd  = (string) ($item['edizione'] ?? '');
            $labelEd   = match ($codiceEd) {
                'basic'   => 'Basic Edition',
                'limited' => 'Limited Edition',
                default   => ucfirst($codiceEd) . ' Edition',
            };

            // ID HTML sicuro (niente caratteri speciali)
            $idSafe = htmlspecialchars($chiave, ENT_QUOTES, 'UTF-8');
    ?>

        <div class="riga-carrello" id="riga-<?= $idSafe ?>">
            <img src="<?= htmlspecialchars($item['img']) ?>"
                 alt="<?= htmlspecialchars($item['titolo']) ?>">

            <div class="riga-info">
                <p class="riga-titolo"><?= htmlspecialchars($item['titolo']) ?></p>
                <p class="riga-artista"><?= htmlspecialchars($item['artista']) ?></p>
                <p class="riga-edizione"><?= htmlspecialchars($labelEd) ?></p>
                <p class="riga-qta-text">Quantità nel carrello:
                    <strong><?= (int) $item['quantita'] ?></strong>
                </p>
                <p class="riga-prezzo">
                    € <?= number_format((float) $item['prezzo'], 2, '.', '') ?> cad.
                </p>
            </div>

            <div class="riga-quantita">
                <button type="button"
                    onclick='cambiaQ("diminuisci", <?= json_encode($chiave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>−</button>
                <span id="q-<?= $idSafe ?>"><?= (int) $item['quantita'] ?></span>
                <button type="button"
                    onclick='cambiaQ("aumenta", <?= json_encode($chiave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>+</button>
            </div>

            <div class="riga-subtotale">
                <!-- FIX: separatore decimale punto, coerente con toFixed(2) in JS -->
                € <span id="sub-<?= $idSafe ?>"><?= number_format($subtotale, 2, '.', '') ?></span>
            </div>

            <button type="button" class="btn-rimuovi"
                onclick='cambiaQ("rimuovi", <?= json_encode($chiave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>🗑</button>
        </div>

    <?php endforeach; ?>

        <div class="carrello-footer">
            <p class="totale-carrello">
                Totale: €&nbsp;<span id="totale-generale"><?= number_format($totalePrezzo, 2, '.', '') ?></span>
            </p>
            <button class="btn-acquista" onclick="effettuaOrdine()">Acquista</button>
        </div>

        <!-- Dati per il JS: prezzi e quantità iniziali -->
        <script>
        const prezziItem = {
        <?php foreach ($_SESSION['carrello'] as $chiaveRaw => $item):
            $vId2   = (int) ($item['vinile_id']   ?? 0);
            $eId2   = (int) ($item['edizione_id'] ?? 0);
            $chiave2= ($vId2 > 0 && $eId2 > 0) ? makeChiave($vId2, $eId2) : $chiaveRaw;
        ?>
            <?= json_encode((string) $chiave2, JSON_HEX_TAG | JSON_HEX_APOS) ?>:
                { prezzo: <?= (float) $item['prezzo'] ?>, quantita: <?= (int) $item['quantita'] ?> },
        <?php endforeach; ?>
        };
        </script>

    <?php endif; ?>
</main>

<!-- POPUP ORDINE EFFETTUATO -->
<div id="popup-ordine" class="popup-ordine-overlay" style="display:none">
    <div class="popup-ordine-box">
        <div class="popup-checkmark">✔</div>
        <h2>Ordine effettuato!</h2>
        <p>Grazie per il tuo acquisto<?= isset($_SESSION['utente_nome'])
            ? ', ' . htmlspecialchars($_SESSION['utente_nome'])
            : '' ?>!</p>
        <button onclick="chiudiOrdine()">Continua a fare shopping</button>
    </div>
</div>

<?php renderFooter(); ?>

<script src="script.js"></script>
</body>
</html>
