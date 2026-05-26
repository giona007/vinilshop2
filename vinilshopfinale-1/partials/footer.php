<?php
/*
 * partials/footer.php
 * Stampa il <footer> del sito con nome del brand, tagline e
 * link alla pagina "Chi siamo".
 */
if (!function_exists('renderFooter')) {
    function renderFooter(): void
    {
        ?>
        <footer class="site-footer" role="contentinfo">
            <div class="footer-inner">
                <div class="footer-top">
                    <!-- Brand e tagline -->
                    <div class="footer-brand">
                        <span class="footer-brand-name">Vinil Shop</span>
                        <span class="footer-brand-line" aria-hidden="true"></span>
                        <span class="footer-brand-tag">vinili · artisti · catalogo</span>
                    </div>
 
                    <!-- Link a "Chi siamo" -->
                    <a href="chi-siamo.php" class="footer-link-chi-siamo">
                        Chi siamo
                        <span class="footer-link-arrow" aria-hidden="true">→</span>
                    </a>
                </div>
 
                <p class="footer-disclaimer">
                    Progetto didattico: catalogo dimostrativo, profili artista e carrello di esempio.
                    Nessun acquisto reale e nessun dato commerciale.
                </p>
            </div>
        </footer>
        <?php
    }
}
