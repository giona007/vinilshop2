<?php
/*
 * login.php
 * Gestisce il form di login: valida i campi, cerca l'utente per email e
 * verifica la password con `password_verify`. Se la verifica ha successo
 * salva le informazioni minime in `$_SESSION`.
 */
include('confing.php');
include('partials/template.php');
include('partials/header.php');
include('partials/footer.php');

$errore = "";

if (isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errore = "Inserisci email e password.";
    } else {
        $stmt = $conn->prepare('SELECT utenteID, nome, password FROM utenti WHERE mail = ?');
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['utente_id']   = $row['utenteID'];
                $_SESSION['utente_nome'] = $row['nome'];
                header("Location: index.php");
                exit;
            } else {
                $errore = "Email o password errati.";
            }
        } else {
            $errore = "Email o password errati.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php renderHead('VinilShop - Login'); ?>
</head>
<body>

<?php renderHeader(); ?>

<main>
    <div class="form-container">
        <h2>Accedi al tuo account</h2>

        <?php if ($errore): ?>
            <p class="msg-errore"><?= htmlspecialchars($errore) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Email</label>
            <input type="email" name="email" required placeholder="inserisci la tua email">

            <label>Password</label>
            <input type="password" name="password" required placeholder="inserisci la tua password">

            <button type="submit">Accedi</button>
        </form>

        <p>Nuovo cliente? <a href="registrazione.php">Registrati</a></p>
    </div>
</main>

    <?php renderFooter(); ?>
</body>
</html>
