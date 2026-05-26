<?php

include('confing.php');
header('Content-Type: application/json');
 
// Inizializza il carrello in sessione se non c'è ancora
$_SESSION['carrello'] ??= [];
 
// Blocca le richieste di utenti non loggati
if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Non autenticato']);
    exit;
}
 
// ---------- Funzioni helper ----------
 
/*
 * Controlla se una tabella esiste nel database.
 * Usata per gestire due versioni dello schema (vecchio/nuovo).
 */
function hasTable(mysqli $conn, string $table): bool
{
    $safe   = $conn->real_escape_string($table);
    $result = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $result && $result->num_rows > 0;
}
 
/*
 * Genera la chiave univoca di un articolo: "vinileId_edizioneId"
 * Deve coincidere con makeChiave() in carrello.php.
 */
function makeChiave(int $vId, int $eId): string
{
    return $vId . '_' . $eId;
}
 
/*
 * Somma tutte le quantità nel carrello → usata per il badge dell'header.
 */
function cartTotalItems(array $carrello): int
{
    return array_sum(array_column($carrello, 'quantita'));
}
 
// ---------- Gestione azione ----------
 
$azione = $_POST['azione'] ?? $_GET['azione'] ?? '';
 
switch ($azione) {
 
    // ── AGGIUNGI ────────────────────────────────────────────
    case 'aggiungi':
        $vinileId   = (int)   ($_POST['vinile_id']   ?? 0);
        $edizioneId = (int)   ($_POST['edizione_id'] ?? 0);
        $titolo     = trim(    $_POST['titolo']       ?? '');
        $artista    = trim(    $_POST['artista']      ?? '');
        $img        = trim(    $_POST['img']          ?? '');
        $edizione   = trim(    $_POST['edizione']     ?? 'basic');
        $prezzo     = (float) ($_POST['prezzo']       ?? 0);
        $quantita   = max(1, (int) ($_POST['quantita'] ?? 1));
 
        $newSchema = hasTable($conn, 'vinili_edizioni');
 
        // Schema nuovo: richiede IDs validi
        if ($newSchema && ($vinileId <= 0 || $edizioneId <= 0)) {
            echo json_encode(['ok' => false, 'msg' => 'Prodotto non valido: aggiorna la pagina e riprova.']);
            exit;
        }
 
        // Schema vecchio (fallback): chiave basata su hash titolo+edizione
        if (!$newSchema || $vinileId <= 0 || $edizioneId <= 0) {
            $chiave = md5($titolo . $edizione);
            if (isset($_SESSION['carrello'][$chiave])) {
                $_SESSION['carrello'][$chiave]['quantita'] += $quantita;
            } else {
                $_SESSION['carrello'][$chiave] = compact('titolo', 'artista', 'img', 'edizione', 'prezzo') + [
                    'vinile_id'   => $vinileId ?: null,
                    'edizione_id' => $edizioneId ?: null,
                    'quantita'    => $quantita,
                ];
            }
            break;
        }
 
        // Schema nuovo: verifica edizione nel DB e usa il prezzo ufficiale
        $stmt = $conn->prepare(
            "SELECT codice, prezzo FROM vinili_edizioni WHERE edizioneID = ? AND vinileID = ?"
        );
        $stmt->bind_param("ii", $edizioneId, $vinileId);
        $stmt->execute();
        $dbEd = $stmt->get_result()->fetch_assoc();
        $stmt->close();
 
        if (!$dbEd) {
            echo json_encode(['ok' => false, 'msg' => 'Edizione non trovata']);
            exit;
        }
 
        $chiave = makeChiave($vinileId, $edizioneId);
 
        if (isset($_SESSION['carrello'][$chiave])) {
            $_SESSION['carrello'][$chiave]['quantita'] += $quantita;
        } else {
            $_SESSION['carrello'][$chiave] = [
                'vinile_id'   => $vinileId,
                'edizione_id' => $edizioneId,
                'titolo'      => $titolo,
                'artista'     => $artista,
                'img'         => $img,
                // Usa il codice dal DB; se vuoto usa quello inviato dal client
                'edizione'    => $dbEd['codice'] ?: $edizione,
                // Usa il prezzo dal DB; se 0 usa quello inviato dal client
                'prezzo'      => (float) $dbEd['prezzo'] ?: $prezzo,
                'quantita'    => $quantita,
            ];
        }
        break;
 
    // ── AUMENTA / DIMINUISCI / RIMUOVI ──────────────────────
    case 'aumenta':
    case 'diminuisci':
    case 'rimuovi':
        $chiave = $_POST['chiave'] ?? '';
 
        if ($azione === 'aumenta' && isset($_SESSION['carrello'][$chiave])) {
            $_SESSION['carrello'][$chiave]['quantita']++;
 
        } elseif ($azione === 'diminuisci' && isset($_SESSION['carrello'][$chiave])) {
            $_SESSION['carrello'][$chiave]['quantita']--;
            // Rimuove l'articolo se la quantità scende a 0
            if ($_SESSION['carrello'][$chiave]['quantita'] <= 0) {
                unset($_SESSION['carrello'][$chiave]);
            }
 
        } elseif ($azione === 'rimuovi') {
            unset($_SESSION['carrello'][$chiave]);
        }
        break;
 
    // ── CHECKOUT ────────────────────────────────────────────
    case 'checkout':
        // Se le tabelle non esistono (ambiente di test) svuota e ritorna ok
        if (!hasTable($conn, 'ordini') || !hasTable($conn, 'dettagli_ordini') || !hasTable($conn, 'vinili_edizioni')) {
            $_SESSION['carrello'] = [];
            echo json_encode(['ok' => true, 'totale' => 0, 'ordine_id' => null]);
            exit;
        }
 
        if (empty($_SESSION['carrello'])) {
            echo json_encode(['ok' => false, 'msg' => 'Carrello vuoto']);
            exit;
        }
 
        // Usa una transazione per garantire che l'ordine sia inserito
        // completamente o non sia inserito affatto
        $conn->begin_transaction();
        try {
            $totaleOrdine = 0.0;
            $righe        = [];
 
            foreach ($_SESSION['carrello'] as $item) {
                $vId  = (int) ($item['vinile_id']   ?? 0);
                $eId  = (int) ($item['edizione_id'] ?? 0);
                $qta  = (int) ($item['quantita']    ?? 0);
 
                if ($vId <= 0 || $eId <= 0 || $qta <= 0) {
                    throw new Exception('Articolo non valido nel carrello');
                }
 
                // Legge il prezzo dal DB con FOR UPDATE per evitare
                // modifiche concorrenti durante il checkout
                $stmt = $conn->prepare(
                    "SELECT prezzo FROM vinili_edizioni
                     WHERE edizioneID = ? AND vinileID = ? FOR UPDATE"
                );
                $stmt->bind_param("ii", $eId, $vId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
 
                if (!$row) throw new Exception('Edizione non trovata');
 
                $prezzo     = (float) $row['prezzo'];
                $subtotale  = round($prezzo * $qta, 2);
                $totaleOrdine += $subtotale;
 
                $righe[] = ['vId' => $vId, 'eId' => $eId, 'qta' => $qta,
                            'prezzo' => $prezzo, 'sub' => $subtotale];
            }
 
            // Inserisce l'ordine
            $uId = (int) $_SESSION['utente_id'];
            $stmt = $conn->prepare("INSERT INTO ordini (utenteID, importoTotale) VALUES (?, ?)");
            $stmt->bind_param("id", $uId, $totaleOrdine);
            $stmt->execute();
            $ordineId = (int) $stmt->insert_id;
            $stmt->close();
 
            // Inserisce le righe di dettaglio
            $stmt = $conn->prepare(
                "INSERT INTO dettagli_ordini
                    (ordineID, vinileID, edizioneID, quantita, prezzoUnitario, subtotale)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            foreach ($righe as $r) {
                $stmt->bind_param("iiiidd", $ordineId, $r['vId'], $r['eId'], $r['qta'], $r['prezzo'], $r['sub']);
                $stmt->execute();
                if ($stmt->error) throw new Exception('Errore dettaglio: ' . $stmt->error);
            }
            $stmt->close();
 
            $_SESSION['carrello'] = [];
            $conn->commit();
 
            echo json_encode(['ok' => true, 'totale' => 0, 'ordine_id' => $ordineId]);
            exit;
 
        } catch (Throwable $e) {
            $conn->rollback();
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
            exit;
        }
 
    // ── SVUOTA ──────────────────────────────────────────────
    case 'svuota':
        $_SESSION['carrello'] = [];
        break;
}
 
// Risposta standard per azioni che non fanno exit() prima
echo json_encode(['ok' => true, 'totale' => cartTotalItems($_SESSION['carrello'])]);
exit;
