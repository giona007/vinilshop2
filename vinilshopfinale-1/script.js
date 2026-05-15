// ============================================================
//  script.js  -  VinilShop
//  Funzioni client-side per: modali, gestione carrello via fetch/AJAX,
//  validazioni minime dei form e piccoli helper UI (toast, filtri).
// ============================================================


// Cancella il testo segnaposto quando l'utente clicca nel campo
function cancella(campo, testoIniziale) {
    if (campo.value === testoIniziale) {
        campo.value = '';
    }
}


// ── REGISTRAZIONE ────────────────────────────────────────

function registra() {
    let nome      = document.getElementById('nome').value;
    let cognome   = document.getElementById('cognome').value;
    let ncivico   = document.getElementById('ncivico').value;
    let via       = document.getElementById('via').value;
    let cap       = document.getElementById('cap').value;
    let provincia = document.getElementById('provincia').value;
    let mail      = document.getElementById('reg_mail').value;
    let password  = document.getElementById('reg_password').value;
    let telefono  = document.getElementById('telefono').value;

    if (nome === '' || nome === 'inserire nome' ||
        mail === '' || mail === 'inserire mail' ||
        password === '' || password === 'inserire password') {
        document.getElementById('msg_reg').innerHTML = 'Compila tutti i campi obbligatori.';
        return;
    }

    localStorage.setItem('nome',      nome);
    localStorage.setItem('cognome',   cognome);
    localStorage.setItem('ncivico',   ncivico);
    localStorage.setItem('via',       via);
    localStorage.setItem('cap',       cap);
    localStorage.setItem('provincia', provincia);
    localStorage.setItem('mail',      mail);
    localStorage.setItem('password',  password);
    localStorage.setItem('telefono',  telefono);

    alert('Registrazione completata!');
    window.location.href = 'login.html';
}


// ── LOGIN ────────────────────────────────────────────────

function login() {
    let mail     = document.getElementById('login_mail').value;
    let password = document.getElementById('login_password').value;

    if (mail === '' || mail === 'inserire mail' ||
        password === '' || password === 'inserire password') {
        document.getElementById('msg_login').innerHTML = 'Inserisci email e password.';
        return;
    }

    let mailSalvata     = localStorage.getItem('mail');
    let passwordSalvata = localStorage.getItem('password');

    if (mail === mailSalvata && password === passwordSalvata) {
        let nomeSalvato = localStorage.getItem('nome');
        alert('Login riuscito! Benvenuto/a, ' + nomeSalvato + '!');
        window.location.href = 'index.html';
    } else {
        document.getElementById('msg_login').innerHTML = 'Email o password errati.';
    }
}


// ── ARTISTI - MODALE ARTISTA ─────────────────────────────

function apriArtista(card) {
    document.getElementById('a-nome').textContent = card.dataset.nome;
    document.getElementById('a-img').src          = card.dataset.img;
    document.getElementById('a-img').alt          = card.dataset.nome;
    // Attenzione: i valori in `data-spotify` / `data-apple` possono essere
    // offuscati lato server; qui si assegna direttamente l'href. Se si
    // utilizza base64 lato server, è necessario decodificare prima.
    document.getElementById('a-spotify').href     = card.dataset.spotify;
    document.getElementById('a-apple').href       = card.dataset.apple;
    document.getElementById('modale-artista').style.display = 'flex';
}

function chiudiArtista() {
    document.getElementById('modale-artista').style.display = 'none';
}


// ── CATALOGO - MODALE ACQUISTO ───────────────────────────

let albumCorrente = {};

function apriModale(card) {
    albumCorrente = {
        vinileId : parseInt(card.dataset.vinileId, 10),
        titolo   : card.dataset.titolo,
        artista  : card.dataset.artista,
        img      : card.dataset.img,
        basicId  : parseInt(card.dataset.edizioneBasicId, 10),
        basic    : parseFloat(card.dataset.basic),
        limitedId: parseInt(card.dataset.edizioneLimitedId, 10),
        limited  : parseFloat(card.dataset.limited),
    };

    document.getElementById('m-img').src                    = albumCorrente.img;
    document.getElementById('m-titolo').textContent         = albumCorrente.titolo;
    document.getElementById('m-artista').textContent        = '(' + albumCorrente.artista + ')';
    // Imposta i valori numerici formattati nella UI della modale
    document.getElementById('m-prezzo-basic').textContent   = albumCorrente.basic.toFixed(2);
    document.getElementById('m-prezzo-limited').textContent = albumCorrente.limited.toFixed(2);
    document.getElementById('m-quantita').value             = 1;

    document.querySelector('input[name="edizione"][value="basic"]').checked = true;
    _aggiornaPrezzoDisplay();

    document.getElementById('modale-acquisto').style.display = 'flex';
}

function chiudiModale() {
    document.getElementById('modale-acquisto').style.display = 'none';
}

// Aggiorna solo il testo del prezzo in base all'edizione selezionata.
function _aggiornaPrezzoDisplay() {
    const edizione = document.querySelector('input[name="edizione"]:checked').value;
    const prezzo   = edizione === 'basic' ? albumCorrente.basic : albumCorrente.limited;
    document.getElementById('m-prezzo-display').textContent = prezzo.toFixed(2);
}

function aggiornaPrezzo() {
    _aggiornaPrezzoDisplay();
}

function aggiungiAlCarrello() {
    const edizione   = document.querySelector('input[name="edizione"]:checked').value;
    const prezzo     = edizione === 'basic' ? albumCorrente.basic : albumCorrente.limited;
    const edizioneId = edizione === 'basic' ? albumCorrente.basicId : albumCorrente.limitedId;

    const quantitaInput = document.getElementById('m-quantita');
    const quantita      = Math.max(1, parseInt(quantitaInput.value, 10) || 1);
    quantitaInput.value = quantita;

    // Invia la richiesta al backend con fetch (POST x-www-form-urlencoded)
    // Il backend risponde con JSON contenente `ok` e `totale`.
    fetch('carrello_action.php', {
        method     : 'POST',
        credentials: 'same-origin',
        headers    : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body       : new URLSearchParams({
            azione      : 'aggiungi',
            vinile_id   : albumCorrente.vinileId,
            edizione_id : edizioneId,
            titolo      : albumCorrente.titolo,
            artista     : albumCorrente.artista,
            img         : albumCorrente.img,
            edizione    : edizione,
            prezzo      : prezzo,
            quantita    : quantita,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            chiudiModale();
            mostraToast('popup-aggiunto', 2500, 'Aggiunti ' + quantita + ' pezzi al carrello.');
            // Aggiorna badge header
            const badge = document.querySelector('.badge-carrello');
            if (badge) {
                badge.textContent = data.totale;
            } else if (data.totale > 0) {
                const cartIcon = document.querySelector('.icona-carrello');
                if (cartIcon) {
                    const nuovoBadge = document.createElement('span');
                    nuovoBadge.className   = 'badge-carrello';
                    nuovoBadge.textContent = data.totale;
                    cartIcon.appendChild(nuovoBadge);
                }
            }
        } else {
            // Mostra errore proveniente dal backend (es: non autenticato)
            alert(data.msg || 'Impossibile aggiungere questo articolo');
        }
    })
    .catch(() => alert('Errore di rete. Riprova.'));
}

function filtraGenere(genere) {
    document.querySelectorAll('.card-album').forEach(card => {
        card.style.display =
            (genere === 'tutti' || card.dataset.genere === genere) ? '' : 'none';
    });
}


// ── CARRELLO ─────────────────────────────────────────────

function cambiaQ(azione, chiave) {
    fetch('carrello_action.php', {
        method     : 'POST',
        credentials: 'same-origin',
        headers    : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body       : new URLSearchParams({ azione, chiave }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) {
            alert(data.msg || 'Operazione non disponibile');
            return;
        }

        if (azione === 'rimuovi') {
            const riga = document.getElementById('riga-' + chiave);
            if (riga) riga.remove();
            delete prezziItem[chiave];
        } else {
            const delta = azione === 'aumenta' ? 1 : -1;
            prezziItem[chiave].quantita += delta;

            if (prezziItem[chiave].quantita <= 0) {
                const riga = document.getElementById('riga-' + chiave);
                if (riga) riga.remove();
                delete prezziItem[chiave];
            } else {
                document.getElementById('q-'   + chiave).textContent =
                    prezziItem[chiave].quantita;
                const sub = prezziItem[chiave].prezzo * prezziItem[chiave].quantita;
                // FIX: usa punto decimale (toFixed) coerente con la visualizzazione JS
                document.getElementById('sub-' + chiave).textContent = sub.toFixed(2);
            }
        }

        // Ricalcola totale
        let tot = 0;
        for (const k in prezziItem) tot += prezziItem[k].prezzo * prezziItem[k].quantita;
        const elTot = document.getElementById('totale-generale');
        if (elTot) elTot.textContent = tot.toFixed(2);

        // Se carrello vuoto ricarica per mostrare il messaggio "carrello vuoto"
        if (Object.keys(prezziItem).length === 0) location.reload();
    })
    .catch(() => alert('Errore di rete. Riprova.'));
}

function effettuaOrdine() {
    fetch('carrello_action.php', {
        method     : 'POST',
        credentials: 'same-origin',
        headers    : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body       : new URLSearchParams({ azione: 'checkout' }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) {
            alert(data.msg || 'Errore durante il checkout');
            return;
        }
        document.getElementById('popup-ordine').style.display = 'flex';
    })
    .catch(() => alert('Errore di rete durante il checkout. Riprova.'));
}

// FIX: dopo il checkout il carrello è vuoto, torna al catalogo invece di
// ricaricare carrello.php che mostrerebbe solo "carrello vuoto"
function chiudiOrdine() {
    window.location.href = 'catalogo.php';
}


// ── UTILITY ──────────────────────────────────────────────

/**
 * Mostra un toast temporaneo per `ms` millisecondi.
 * @param {string}      id    id dell'elemento toast
 * @param {number}      ms    durata in ms (default 2500)
 * @param {string|null} testo testo da mostrare in .toast-msg (opzionale)
 */
function mostraToast(id, ms = 2500, testo = null) {
    const el = document.getElementById(id);
    if (!el) return;
    if (testo) {
        const msg = el.querySelector('.toast-msg');
        if (msg) msg.textContent = testo;
    }
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, ms);
}