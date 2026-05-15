<?php
/*
 * registrazione.php
 * Form di registrazione utente. Valida i campi minimi, controlla duplicati
 * di email usando una query preparata e salva la password con
 * `password_hash` prima di inserire il record.
 */
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');

$errore = "";

if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: registrazione.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome']      ?? '');
    $cognome   = trim($_POST['cognome']   ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = trim($_POST['password']  ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $via       = trim($_POST['via']       ?? '');
    $ncivico   = trim($_POST['ncivico']   ?? '');

    if ($nome === '' || $email === '' || $password === '') {
        $errore = "Compila tutti i campi obbligatori (nome, email, password).";
    } else {
        // controlla se la mail esiste già
        $check = $conn->prepare("SELECT utenteID FROM utenti WHERE mail = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errore = "Esiste già un account con questa email.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO utenti (nome, cognome, mail, password, telefono, via, numeroCivico)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssssss",
                $nome, $cognome, $email, $hash, $telefono, $via, $ncivico
            );

            if ($stmt->execute()) {
                $_SESSION['utente_id']   = $conn->insert_id;
                $_SESSION['utente_nome'] = $nome;
                header("Location: index.php");
                exit;
            } else {
                $errore = "Errore durante la registrazione. Riprova.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Registrazione'); ?>
</head>
<body>

<?php renderHeader(); ?>

<main>
    <div class="form-container">
        <h2>Crea il tuo account</h2>

        <?php if ($errore): ?>
            <p class="msg-errore"><?= htmlspecialchars($errore) ?></p>
        <?php endif; ?>

        <form method="POST" action="registrazione.php">
            <label>Nome *</label>
            <input type="text" name="nome" required placeholder="inserisci il tuo nome">

            <label>Cognome</label>
            <input type="text" name="cognome" placeholder="inserisci il tuo cognome">

            <label>Email *</label>
            <input type="email" name="email" required placeholder="inserisci la tua email">

            <label>Password *</label>
            <input type="password" name="password" required placeholder="scegli una password">

            <label>Numero di telefono</label>
            <input type="text" name="telefono" placeholder="es. 3331234567">

            <label>Via</label>
            <input type="text" name="via" placeholder="inserisci la via">

            <label>N° Civico</label>
            <input type="text" name="ncivico" placeholder="es. 12">

            <button type="submit">Registrati</button>
        </form>

        <p>Hai già un account? <a href="login.php">Accedi</a></p>
    </div>
</main>

    <?php renderFooter(); ?>
</body>
</html>
