<?php

// Partial che stampa l'header del sito (logo, menu, icone).
// `renderHeader()` è idempotente e usa `cartItemsCount()` per mostrare
// il badge del carrello, se necessario.
if (!function_exists('renderHeader')) {
    function renderHeader(string $activePage = ''): void
    {
        $menuItems = [
            'home' => ['label' => 'home', 'href' => 'index.php'],
            'artisti' => ['label' => 'artisti', 'href' => 'artisti.php'],
            'catalogo' => ['label' => 'catalogo', 'href' => 'catalogo.php'],
        ];
        // Conteggio elementi nel carrello (funzione definita in template.php)
        $cartCount = cartItemsCount();
        ?>
        <header>
            <a class="logo" href="index.php" aria-label="Vai alla home">
                <img src="immagini/vinile.png" alt="Vinil Shop Logo">
                <span>Vinil Shop</span>
            </a>
            <nav>
                <ul>
                    <?php foreach ($menuItems as $key => $item): ?>
                        <li>
                            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $activePage === $key ? 'is-active' : '' ?>">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="nav-icons">
                <?php if (isset($_SESSION['utente_id'])): ?>
                    <span class="benvenuto">👤 <?= htmlspecialchars($_SESSION['utente_nome']) ?></span>
                    <form method="POST" class="logout-form">
                        <button type="submit" name="logout" class="btn-logout">Esci</button>
                    </form>
                <?php else: ?>
                    <a href="login.php">👤 Accedi</a>
                <?php endif; ?>
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
