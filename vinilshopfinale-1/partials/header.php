<?php
/*
 * partials/header.php
 * Stampa l'<header> del sito: logo, menu di navigazione e icone
 * (login/logout e carrello con badge).
 *
 * Uso: renderHeader('catalogo')  → evidenzia la voce "catalogo" nel menu
 */
if (!function_exists('renderHeader')) {
    function renderHeader(string $activePage = ''): void
    {
        // Voci del menu principale
        $menu = [
            'home'     => ['label' => 'Home',     'href' => 'index.php'],
            'artisti'  => ['label' => 'Artisti',  'href' => 'artisti.php'],
            'catalogo' => ['label' => 'Catalogo', 'href' => 'catalogo.php'],
        ];
 
        // Numero di pezzi totali nel carrello (per il badge rosso)
        $cartCount = cartItemsCount();
        ?>
        <header>
            <!-- Logo cliccabile che porta alla home -->
            <a class="logo" href="index.php" aria-label="Vai alla home">
                <img src="immagini/vinile.png" alt="Vinil Shop Logo">
                <span>Vinil Shop</span>
            </a>
 
            <!-- Menu di navigazione -->
            <nav>
                <ul>
                    <?php foreach ($menu as $key => $item): ?>
                        <li>
                            <a href="<?= htmlspecialchars($item['href']) ?>"
                               class="<?= $activePage === $key ? 'is-active' : '' ?>">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
 
            <!-- Icone destra: utente e carrello -->
            <div class="nav-icons">
                <?php if (isset($_SESSION['utente_id'])): ?>
                    <!-- Utente loggato: mostra nome e pulsante logout -->
                    <span class="benvenuto">👤 <?= htmlspecialchars($_SESSION['utente_nome']) ?></span>
                    <form method="POST" class="logout-form">
                        <button type="submit" name="logout" class="btn-logout">Esci</button>
                    </form>
                <?php else: ?>
                    <!-- Utente non loggato: link alla pagina di login -->
                    <a href="login.php">👤 Accedi</a>
                <?php endif; ?>
 
                <!-- Icona carrello con badge se ci sono prodotti -->
                <a href="carrello.php" class="icona-carrello" aria-label="Vai al carrello">
                    🛒
                    <?php if ($cartCount > 0): ?>
                        <span class="badge-carrello"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>
        <?php
    }
}
