<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateur = $authController->utilisateurConnecte();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($utilisateur) {
    if ($authController->estAdmin($utilisateur)) {
        header('Location: backoffice.php');
    } else {
        header('Location: home.php');
    }
    exit();
}

$messagesErreur = [
    'invalid_email' => 'Adresse email invalide.',
    'email_send_failed' => 'Envoi de l\'email impossible. Verifiez la configuration SMTP du serveur.',
    'password_mismatch' => 'Les mots de passe ne correspondent pas.',
    'password_too_short' => 'Le mot de passe doit contenir au moins 6 caracteres.',
    'invalid_code' => 'Code invalide.',
    'code_expired' => 'Code expire. Veuillez en demander un nouveau.',
    'email_not_found' => 'Aucun compte n\'est associe a cet email.',
    'server_error' => 'Erreur serveur. Veuillez reessayer.',
    'reset_request_failed' => 'La demande de reinitialisation a echoue.',
    'reset_failed' => 'La reinitialisation du mot de passe a echoue.'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kool Healthy - Mot de passe oublie</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/auth.css">
</head>
<body>
    <a class="page-logo" href="home.php" aria-label="Kool Healthy">
        <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
    </a>

    <main class="auth-page">
        <section class="auth-card">
            <?php if ($success === 'code_sent'): ?>
                <div class="alert success">Mail envoyé.</div>
            <?php endif; ?>

            <?php if ($error !== '' && isset($messagesErreur[$error])): ?>
                <div class="alert error"><?php echo htmlspecialchars($messagesErreur[$error]); ?></div>
            <?php endif; ?>

            <section class="auth-box">
                <h2>Mot de passe oublie</h2>
                <form method="POST" action="../CONTROLLER/AuthController.php?action=request_password_reset" novalidate>
                    <input type="email" name="email" placeholder="Votre email" required>
                    <button type="submit">Envoyer le code</button>
                </form>

                <div class="oauth-divider"><span>code recu ?</span></div>

                <form method="POST" action="../CONTROLLER/AuthController.php?action=reset_password" novalidate>
                    <?php $prefillEmail = htmlspecialchars($_GET['email'] ?? ''); ?>
                    <input type="hidden" name="email" value="<?php echo $prefillEmail; ?>">
                    <input type="text" name="code" placeholder="Code a 6 chiffres" minlength="6" maxlength="6" required>
                    <input type="password" name="nouveau_mot_de_passe" placeholder="Nouveau mot de passe" minlength="6" required>
                    <input type="password" name="confirmer_mot_de_passe" placeholder="Confirmer le mot de passe" minlength="6" required>
                    <button type="submit">Changer le mot de passe</button>
                </form>

                <p class="switch-link">Retour a la <a href="auth.php">connexion</a>.</p>
            </section>
        </section>
    </main>
</body>
</html>
