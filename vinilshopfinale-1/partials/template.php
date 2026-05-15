<?php

// Partial con helper comuni: `renderHead()` genera i meta tag e il CSS
// mentre `cartItemsCount()` somma le quantità presenti in `$_SESSION['carrello']`.
if (!function_exists('renderHead')) {
    function renderHead(string $title): void
    {
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>' . htmlspecialchars($title) . '</title>';
        echo '<link rel="stylesheet" href="mystyle.css">';
    }
}

if (!function_exists('cartItemsCount')) {
    function cartItemsCount(): int
    {
        $total = 0;

        if (!empty($_SESSION['carrello'])) {
            foreach ($_SESSION['carrello'] as $item) {
                // Assicuriamoci che quantita sia sempre un intero
                $total += (int) ($item['quantita'] ?? 0);
            }
        }

        return $total;
    }
}

