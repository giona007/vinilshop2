<?php
/*
 * carrello_action.php
 * Punto di ingresso AJAX per le operazioni sul carrello (aggiungi, aumenta,
 * diminuisci, rimuovi, checkout, svuota). Restituisce JSON con lo stato
 * dell'operazione. Le operazioni che manipolano il DB usano transazioni
 * quando necessario (checkout) per garantire coerenza.
 */
include('confing.php');
header('Content-Type: application/json');

// Controlla se una tabella esiste nel DB (evita query con nomi non sanitizzati)
function hasTable(mysqli $conn, string $tableName): bool
{
    $safeName = $conn->real_escape_string($tableName);
    $result   = $conn->query("SHOW TABLES LIKE '{$safeName}'");
    if (!$result) {
        return false;
    }
    $exists = $result->num_rows > 0;
    $result->free();
    return $exists;
}

// Costruisce la chiave univoca usata nelle sessioni carrello per identificare
// una combinazione vinile+edizione (es: "12_3"). Usata anche nel frontend.
function makeChiave(int $vinileId, int $edizioneId): string
{
    return $vinileId . '_' . $edizioneId;
}

$azione = $_POST['azione'] ?? $_GET['azione'] ?? '';

// Inizializza il carrello in sessione se non esiste
if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Non autenticato']);
    exit;
}

// Ritorna il numero totale di pezzi nel carrello (somma delle quantità)
function cartTotalItems(array $carrello): int
{
    $tot = 0;
    foreach ($carrello as $item) {
        $tot += (int) ($item['quantita'] ?? 0);
    }
    return $tot;
}

switch ($azione) {

    case 'aggiungi':
        $vinileId          = (int) ($_POST['vinile_id']   ?? 0);
        $edizioneId        = (int) ($_POST['edizione_id'] ?? 0);
        $titolo            = trim($_POST['titolo']       ?? '');
        $artista           = trim($_POST['artista']      ?? '');
        $img               = trim($_POST['img']          ?? '');
        $edizione          = trim($_POST['edizione']     ?? 'basic');
        $prezzo            = (float) ($_POST['prezzo']       ?? 0);
        $quantitaRichiesta = max(1, (int) ($_POST['quantita']   ?? 1));

        $newSchema = hasTable($conn, 'vinili_edizioni');

        if ($newSchema && ($vinileId <= 0 || $edizioneId <= 0)) {
            echo json_encode(['ok' => false, 'msg' => 'Prodotto non valido: aggiorna la pagina e riprova.']);
            exit;
        }

        if (!$newSchema || $vinileId <= 0 || $edizioneId <= 0) {
            $chiave = md5($titolo . $edizione);
            if (isset($_SESSION['carrello'][$chiave])) {
                $_SESSION['carrello'][$chiave]['quantita'] += $quantitaRichiesta;
            } else {
                $_SESSION['carrello'][$chiave] = [
                    'vinile_id'   => $vinileId > 0 ? $vinileId : null,
                    'edizione_id' => $edizioneId > 0 ? $edizioneId : null,
                    'titolo'      => $titolo,
                    'artista'     => $artista,
                    'img'         => $img,
                    'edizione'    => $edizione,
                    'prezzo'      => $prezzo,
                    'quantita'    => $quantitaRichiesta,
                ];
            }
            break;
        }

        $stmt = $conn->prepare(
            "SELECT edizioneID, codice, nome, prezzo
             FROM vinili_edizioni
             WHERE edizioneID = ? AND vinileID = ?"
        );
        $stmt->bind_param("ii", $edizioneId, $vinileId);
        $stmt->execute();
        $dbEd = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$dbEd) {
            echo json_encode(['ok' => false, 'msg' => 'Edizione non trovata']);
            exit;
        }

        $prezzoDb = (float) $dbEd['prezzo'];
        $codiceEd = (string) $dbEd['codice'];
        $chiave   = makeChiave($vinileId, $edizioneId);

        if (isset($_SESSION['carrello'][$chiave])) {
            $_SESSION['carrello'][$chiave]['quantita'] += $quantitaRichiesta;
        } else {
            $_SESSION['carrello'][$chiave] = [
                'vinile_id'   => $vinileId,
                'edizione_id' => $edizioneId,
                'titolo'      => $titolo,
                'artista'     => $artista,
                'img'         => $img,
                'edizione'    => $codiceEd ?: $edizione,
                'prezzo'      => $prezzoDb > 0 ? $prezzoDb : $prezzo,
                'quantita'    => $quantitaRichiesta,
            ];
        }
        break;

    case 'aumenta':
    case 'diminuisci':
    case 'rimuovi':
        $chiave = $_POST['chiave'] ?? '';
        if ($azione === 'aumenta' && isset($_SESSION['carrello'][$chiave])) {
            $_SESSION['carrello'][$chiave]['quantita']++;
        } elseif ($azione === 'diminuisci' && isset($_SESSION['carrello'][$chiave])) {
            $_SESSION['carrello'][$chiave]['quantita']--;
            if ($_SESSION['carrello'][$chiave]['quantita'] <= 0) {
                unset($_SESSION['carrello'][$chiave]);
            }
        } elseif ($azione === 'rimuovi') {
            unset($_SESSION['carrello'][$chiave]);
        }
        break;

    case 'checkout':
        if (!hasTable($conn, 'ordini') || !hasTable($conn, 'dettagli_ordini') || !hasTable($conn, 'vinili_edizioni')) {
            $_SESSION['carrello'] = [];
            echo json_encode(['ok' => true, 'totale' => 0, 'ordine_id' => null]);
            exit;
        }

        if (empty($_SESSION['carrello'])) {
            echo json_encode(['ok' => false, 'msg' => 'Carrello vuoto']);
            exit;
        }

        $conn->begin_transaction();
        try {
            $totaleOrdine = 0.0;
            $righeOrdine  = [];

            foreach ($_SESSION['carrello'] as $item) {
                $vinileId   = (int) ($item['vinile_id']   ?? 0);
                $edizioneId = (int) ($item['edizione_id'] ?? 0);
                $quantita   = (int) ($item['quantita']    ?? 0);

                if ($vinileId <= 0 || $edizioneId <= 0 || $quantita <= 0) {
                    throw new Exception('Articolo non valido nel carrello');
                }

                $stmt = $conn->prepare(
                    "SELECT edizioneID, prezzo
                     FROM vinili_edizioni
                     WHERE edizioneID = ? AND vinileID = ?
                     FOR UPDATE"
                );
                $stmt->bind_param("ii", $edizioneId, $vinileId);
                $stmt->execute();
                $dbEd = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$dbEd) {
                    throw new Exception('Edizione non trovata');
                }

                $prezzoDb = (float) $dbEd['prezzo'];
                $subtotale = round($prezzoDb * $quantita, 2);
                $totaleOrdine += $subtotale;
                $righeOrdine[] = [
                    'vinile_id'       => $vinileId,
                    'edizione_id'     => $edizioneId,
                    'quantita'        => $quantita,
                    'prezzo_unitario' => $prezzoDb,
                    'subtotale'       => $subtotale,
                ];
            }

            $utenteId = (int) $_SESSION['utente_id'];
            /* Tabella ordini: solo utenteID + importoTotale (+ note opzionali in futuro). */
            $stmtOrdine = $conn->prepare(
                "INSERT INTO ordini (utenteID, importoTotale) VALUES (?, ?)"
            );
            $stmtOrdine->bind_param("id", $utenteId, $totaleOrdine);
            $stmtOrdine->execute();
            $ordineId = (int) $stmtOrdine->insert_id;
            $stmtOrdine->close();

            $stmtDet = $conn->prepare(
                "INSERT INTO dettagli_ordini
                    (ordineID, vinileID, edizioneID, quantita, prezzoUnitario, subtotale)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($righeOrdine as $riga) {
                $stmtDet->bind_param(
                    "iiiidd",
                    $ordineId,
                    $riga['vinile_id'],
                    $riga['edizione_id'],
                    $riga['quantita'],
                    $riga['prezzo_unitario'],
                    $riga['subtotale']
                );
                $stmtDet->execute();
                if ($stmtDet->error) {
                    throw new Exception('Errore dettaglio: ' . $stmtDet->error);
                }
            }

            $stmtDet->close();

            $_SESSION['carrello'] = [];
            $conn->commit();

            echo json_encode(['ok' => true, 'totale' => 0, 'ordine_id' => $ordineId]);
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
            exit;
        }

    case 'svuota':
        $_SESSION['carrello'] = [];
        break;
}

$totale = cartTotalItems($_SESSION['carrello']);
echo json_encode(['ok' => true, 'totale' => $totale]);
exit;
