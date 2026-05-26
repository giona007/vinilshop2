
<?php
/*
 * partials/template.php
 * Helper riutilizzabili inclusi da tutte le pagine:
 *   - renderHead()       → genera i tag <meta> e il <link> al CSS
 *   - cartItemsCount()   → conta il totale pezzi nel carrello (per il badge)
 */
 
// renderHead() viene chiamata dentro <head> da ogni pagina
if (!function_exists('renderHead')) {
    function renderHead(string $title): void
    {
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>' . htmlspecialchars($title) . '</title>';
        echo '<link rel="stylesheet" href="mystyle.css">';
    }
}
 
// Somma le quantità di tutti i prodotti presenti nel carrello in sessione.
// Restituisce 0 se il carrello è vuoto o non esiste.
if (!function_exists('cartItemsCount')) {
    function cartItemsCount(): int
    {
        $total = 0;
        foreach ($_SESSION['carrello'] ?? [] as $item) {
            $total += (int) ($item['quantita'] ?? 0);
        }
        return $total;
    }
}
