<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateur = $authController->utilisateurConnecte();
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kool Healthy - Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/auth.css">
</head>
<body>
    <a class="page-logo" href="index.php" aria-label="Kool Healthy">
        <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
    </a>

    <main class="auth-page">
        <section class="auth-card">
            <?php if ($error === 'register'): ?>
                <div class="alert error">Nom ou email deja utilise.</div>
            <?php endif; ?>

            <?php if ($error === 'password_mismatch'): ?>
                <div class="alert error">Les mots de passe ne correspondent pas.</div>
            <?php endif; ?>

            <?php if ($utilisateur): ?>
                <div class="logged-box">
                    <h2>Bonjour <?php echo htmlspecialchars($utilisateur['nom']); ?></h2>
                    <p><?php echo htmlspecialchars($utilisateur['email']); ?></p>
                    <a class="btn-link" href="../CONTROLLER/AuthController.php?action=logout">Se deconnecter</a>
                    <a class="btn-link secondary" href="index.php">Aller au fil des recettes</a>
                </div>
            <?php else: ?>
                <section class="auth-box">
                    <form method="POST" action="../CONTROLLER/AuthController.php?action=register" id="registerForm">
                        <h2>Inscription</h2>
                        <input type="text" name="nom" placeholder="Nom" required>
                        <input type="email" name="email" placeholder="Mail" required>
                        <input type="text" name="role" placeholder="Role (utilisateur, coach...)" value="utilisateur" required>
                        <input type="number" step="0.1" min="1" name="poids" placeholder="Poids (kg)" required>
                        <input type="number" step="0.01" min="0.5" name="taille" placeholder="Taille (m, ex: 1.75)" required>
                        <input type="text" name="objectif" placeholder="Objectif (perte de poids, maintien...)" required>
                        <input type="number" name="age" min="1" placeholder="Age" required>
                        <input type="text" name="allergies" placeholder="Allergies (ex: arachide, gluten)">
                        <input type="number" min="800" name="besoins_caloriques" placeholder="Besoins caloriques (kcal/jour)" required>
                        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required id="motDePasse">
                        <input type="password" name="confirmer_mot_de_passe" placeholder="Confirmer mot de passe" required id="confirmerMotDePasse">
                        <span id="passwordError" class="password-error" style="display:none;color:red;font-size:0.9rem;margin-bottom:10px;">Les mots de passe ne correspondent pas</span>
                        <button type="submit">S'inscrire</button>
                        <p class="switch-link">Vous avez deja un compte ? <a href="auth.php">Se connecter</a></p>
                    </form>
                </section>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const registerForm = document.getElementById('registerForm');
            const motDePasse = document.getElementById('motDePasse');
            const confirmerMotDePasse = document.getElementById('confirmerMotDePasse');
            const passwordError = document.getElementById('passwordError');

            if (registerForm && motDePasse && confirmerMotDePasse && passwordError) {
                const submitButton = registerForm.querySelector('button[type="submit"]');

                const validatePasswords = function () {
                    const mismatch = motDePasse.value !== confirmerMotDePasse.value;
                    if (confirmerMotDePasse.value.length > 0 && mismatch) {
                        passwordError.style.display = 'block';
                        submitButton.disabled = true;
                    } else {
                        passwordError.style.display = 'none';
                        submitButton.disabled = false;
                    }
                };

                confirmerMotDePasse.addEventListener('input', validatePasswords);
                motDePasse.addEventListener('input', validatePasswords);
            }
        });
    </script>
</body>
</html>
