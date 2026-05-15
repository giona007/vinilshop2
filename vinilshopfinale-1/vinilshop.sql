-- =============================================================================
-- VinilShop — database completo (UNICO FILE SQL)
-- =============================================================================
-- NOTE: questo file crea uno schema di esempio; importalo solo in un DB
-- di sviluppo o in un'istanza dedicata perché contiene DROP DATABASE.
-- In phpMyAdmin: Importa questo file (oppure copia/incolla in SQL).
-- ATTENZIONE: DROP DATABASE cancella tutti i dati esistenti del DB `vinilshop`.
--
-- Schema semplificato:
--   • utenti: niente ruolo, niente created_at
--   • vinili / artisti / edizioni: niente is_active, created_at, magazzino
--   • ordini: solo ordineID, utenteID, importoTotale, note (NO data, NO stato)
--   • dettagli_ordini: righe d’ordine (quantità = pezzi acquistati per riga)
-- =============================================================================

DROP DATABASE IF EXISTS vinilshop;
CREATE DATABASE vinilshop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vinilshop;

CREATE TABLE utenti (
  utenteID INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL,
  cognome VARCHAR(50) DEFAULT NULL,
  via VARCHAR(100) DEFAULT NULL,
  numeroCivico VARCHAR(10) DEFAULT NULL,
  telefono VARCHAR(20) DEFAULT NULL,
  mail VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_utenti_mail (mail)
) ENGINE=InnoDB;

CREATE TABLE artisti (
  artistaID INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  spotify_url VARCHAR(255) DEFAULT NULL,
  apple_music_url VARCHAR(255) DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uq_artisti_slug (slug),
  UNIQUE KEY uq_artisti_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE vinili (
  vinileID INT AUTO_INCREMENT PRIMARY KEY,
  artistaID INT NOT NULL,
  titolo VARCHAR(100) NOT NULL,
  genere VARCHAR(50) NOT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  CONSTRAINT fk_vinili_artista
    FOREIGN KEY (artistaID) REFERENCES artisti(artistaID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_vinili_genere (genere),
  INDEX idx_vinili_artista (artistaID)
) ENGINE=InnoDB;

CREATE TABLE vinili_edizioni (
  edizioneID INT AUTO_INCREMENT PRIMARY KEY,
  vinileID INT NOT NULL,
  codice VARCHAR(20) NOT NULL,
  nome VARCHAR(60) NOT NULL,
  prezzo DECIMAL(8,2) NOT NULL,
  CONSTRAINT fk_edizioni_vinile
    FOREIGN KEY (vinileID) REFERENCES vinili(vinileID)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_vinile_codice (vinileID, codice),
  CHECK (prezzo >= 0)
) ENGINE=InnoDB;

CREATE TABLE ordini (
  ordineID INT AUTO_INCREMENT PRIMARY KEY,
  utenteID INT NOT NULL,
  importoTotale DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  note VARCHAR(255) DEFAULT NULL,
  CONSTRAINT fk_ordini_utente
    FOREIGN KEY (utenteID) REFERENCES utenti(utenteID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_ordini_utente (utenteID)
) ENGINE=InnoDB;

CREATE TABLE dettagli_ordini (
  dettaglioOrdineID INT AUTO_INCREMENT PRIMARY KEY,
  ordineID INT NOT NULL,
  vinileID INT NOT NULL,
  edizioneID INT NOT NULL,
  quantita INT NOT NULL,
  prezzoUnitario DECIMAL(8,2) NOT NULL,
  subtotale DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_dettagli_ordine
    FOREIGN KEY (ordineID) REFERENCES ordini(ordineID)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dettagli_vinile
    FOREIGN KEY (vinileID) REFERENCES vinili(vinileID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_dettagli_edizione
    FOREIGN KEY (edizioneID) REFERENCES vinili_edizioni(edizioneID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CHECK (quantita > 0),
  CHECK (prezzoUnitario >= 0),
  CHECK (subtotale >= 0)
) ENGINE=InnoDB;

INSERT INTO artisti (artistaID, nome, slug, spotify_url, apple_music_url, image_path) VALUES
  (1, '18k', '18k', 'https://open.spotify.com/intl-it/artist/18k', 'https://music.apple.com/it/artist/18k', 'immagini/18k.png'),
  (2, 'Aira', 'aira', 'https://open.spotify.com/intl-it/artist/5bSVRdpMeiqm5FnIMxOa12', 'https://music.apple.com/it/artist/1533624922', 'immagini/aira.png'),
  (3, 'Kid Yugi', 'kid-yugi', 'https://open.spotify.com/intl-it/artist/2BHyLiCjRoFGQGQngGOkHu', 'https://music.apple.com/it/artist/1533624923', 'immagini/kidyugi.png'),
  (4, 'Promessa', 'promessa', 'https://open.spotify.com/intl-it/artist/promessa', 'https://music.apple.com/it/artist/promessa', 'immagini/promessa.png');

INSERT INTO vinili (vinileID, artistaID, titolo, genere, image_path) VALUES
  (1, 1, 'Anti Anti', 'trap', 'immagini/anti-anti.png'),
  (2, 1, 'IO', 'trap', 'immagini/io.png'),
  (3, 2, 'Crash Out', 'rage', 'immagini/crashout.png'),
  (4, 3, 'Anche gli eroi muoiono', 'rap', 'immagini/anche-gli-eroi-muoiono.png'),
  (5, 3, 'The Globe', 'trap', 'immagini/the-globe-baisc.png'),
  (6, 4, 'Morendo ad occhi aperti', 'rap', 'immagini/morendo-ad-occhi-aperti.png');

INSERT INTO vinili_edizioni (edizioneID, vinileID, codice, nome, prezzo) VALUES
  (1, 1, 'basic', 'Basic Edition', 19.99),
  (2, 1, 'limited', 'Limited Edition', 34.99),
  (3, 2, 'basic', 'Basic Edition', 19.99),
  (4, 2, 'limited', 'Limited Edition', 34.99),
  (5, 3, 'basic', 'Basic Edition', 19.99),
  (6, 3, 'limited', 'Limited Edition', 34.99),
  (7, 4, 'basic', 'Basic Edition', 19.99),
  (8, 4, 'limited', 'Limited Edition', 34.99),
  (9, 5, 'basic', 'Basic Edition', 19.99),
  (10, 5, 'limited', 'Limited Edition', 34.99),
  (11, 6, 'basic', 'Basic Edition', 19.99),
  (12, 6, 'limited', 'Limited Edition', 34.99);
-- LOGIN (login.php)
--    Cerca l'utente per email; la verifica della password avviene in PHP
--    con password_verify($password, $row['password'])
-- -----------------------------------------------------------------------------
SELECT utenteID, nome, password
FROM utenti
WHERE mail = ?;
-- -----------------------------------------------------------------------------
-- REGISTRAZIONE — controllo email duplicata (registrazione.php)
--     Se num_rows > 0 l'email è già registrata e si mostra un errore
-- -----------------------------------------------------------------------------
SELECT utenteID
FROM utenti
WHERE mail = ?;
-- -----------------------------------------------------------------------------
-- REGISTRAZIONE — inserimento nuovo utente (registrazione.php)
--     La password viene prima cifrata in PHP con password_hash()
-- -----------------------------------------------------------------------------
INSERT INTO utenti (nome, cognome, mail, password, telefono, via, numeroCivico)
VALUES (?, ?, ?, ?, ?, ?, ?);
-- -----------------------------------------------------------------------------
-- CARRELLO — verifica edizione al momento dell'aggiunta (carrello_action.php)
-- Controlla che l'edizione esista e appartenda al vinile corretto;
-- recupera il prezzo aggiornato dal database
-- -----------------------------------------------------------------------------
SELECT edizioneID, codice, nome, prezzo
FROM vinili_edizioni
WHERE edizioneID = ? AND vinileID = ?;


-- -----------------------------------------------------------------------------
--  CHECKOUT — inserimento ordine (carrello_action.php)
--     Registra che quell'utente ha fatto un ordine, con l'importo totale e eventuali note
-- -----------------------------------------------------------------------------
INSERT INTO ordini (utenteID, importoTotale)
VALUES (?, ?);
-- -----------------------------------------------------------------------------
-- CHECKOUT — inserimento righe dettaglio (carrello_action.php)
--     Eseguita una volta per ogni articolo nel carrello
-- -----------------------------------------------------------------------------
INSERT INTO dettagli_ordini
   (ordineID, vinileID, edizioneID, quantita, prezzoUnitario, subtotale)
VALUES (?, ?, ?, ?, ?, ?);