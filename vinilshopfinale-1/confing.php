<?php
/*
 * confing.php
 * Configurazione minima: avvia la sessione, abilita il reporting degli errori
 * (utile in ambiente di sviluppo) e crea la connessione MySQLi.
 * ATTENZIONE: le credenziali sono presenti nel file per comodità didattica;
 * su sistemi reali vanno spostate in variabili d'ambiente o file non pubblici.
 */
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "fdb1031.freehostingeu.com";
$user = "4696596_vinilshop";
$password = "giona.007";
$db = "4696596_vinilshop";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
