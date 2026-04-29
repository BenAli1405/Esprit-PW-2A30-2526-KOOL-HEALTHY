<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateur = $authController->utilisateurConnecte();
$error = $_GET['error'] ?? '';

if ($utilisateur) {
    if ($authController->estAdmin($utilisateur)) {
        header('Location: backoffice.php');
    } else {
        header('Location: home.php');
    }
    exit();
}
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
    <a class="page-logo" href="home.php" aria-label="Kool Healthy">
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

            <section class="auth-box">
                <form method="POST" action="../CONTROLLER/AuthController.php?action=register" id="registerForm" novalidate>
                    <h2>Inscription</h2>
                    <input type="text" name="nom" placeholder="Nom">
                    <input type="email" name="email" placeholder="Mail">
                    <input type="number" step="0.1" name="poids" placeholder="Poids (kg)">
                    <input type="number" step="0.01" name="taille" placeholder="Taille (m, ex: 1.75)">
                    <input type="number" name="age" placeholder="Age">
                    <input type="text" name="allergies" placeholder="Allergies (ex: arachide, gluten)">
                    <input type="number" name="besoins_caloriques" placeholder="Besoins caloriques (kcal/jour)">
                    <input type="password" name="mot_de_passe" placeholder="Mot de passe" id="motDePasse">
                    <input type="password" name="confirmer_mot_de_passe" placeholder="Confirmer mot de passe" id="confirmerMotDePasse">
                    <span id="registerError" class="password-error" style="display:none;color:red;font-size:0.9rem;margin-bottom:10px;"></span>
                    <span id="passwordError" class="password-error" style="display:none;color:red;font-size:0.9rem;margin-bottom:10px;">Les mots de passe ne correspondent pas</span>
                    <button type="submit">S'inscrire</button>
                    <p class="switch-link">Vous avez deja un compte ? <a href="auth.php">Se connecter</a></p>
                </form>
            </section>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const registerForm = document.getElementById('registerForm');
            const motDePasse = document.getElementById('motDePasse');
            const confirmerMotDePasse = document.getElementById('confirmerMotDePasse');
            const passwordError = document.getElementById('passwordError');
            const registerError = document.getElementById('registerError');

            if (registerForm && motDePasse && confirmerMotDePasse && passwordError && registerError) {
                const submitButton = registerForm.querySelector('button[type="submit"]');

                const isValidEmail = function (email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                };

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

                registerForm.addEventListener('submit', function (event) {
                    const nom = (registerForm.querySelector('input[name="nom"]')?.value || '').trim();
                    const email = (registerForm.querySelector('input[name="email"]')?.value || '').trim();
                    const poids = Number(registerForm.querySelector('input[name="poids"]')?.value || 0);
                    const taille = Number(registerForm.querySelector('input[name="taille"]')?.value || 0);
                    const age = Number(registerForm.querySelector('input[name="age"]')?.value || 0);
                    const besoins = Number(registerForm.querySelector('input[name="besoins_caloriques"]')?.value || 0);
                    const motDePasseValeur = motDePasse.value || '';
                    const confirmerValeur = confirmerMotDePasse.value || '';

                    if (nom === '' || motDePasseValeur === '' || confirmerValeur === '') {
                        event.preventDefault();
                        registerError.textContent = 'Veuillez remplir tous les champs obligatoires.';
                        registerError.style.display = 'block';
                        return;
                    }

                    if (!isValidEmail(email)) {
                        event.preventDefault();
                        registerError.textContent = 'Veuillez saisir une adresse email valide.';
                        registerError.style.display = 'block';
                        return;
                    }

                    if (Number.isNaN(poids) || poids <= 0) {
                        event.preventDefault();
                        registerError.textContent = 'Le poids est invalide (valeur attendue: > 0 kg).';
                        registerError.style.display = 'block';
                        return;
                    }

                    if (Number.isNaN(taille) || taille < 0.5) {
                        event.preventDefault();
                        registerError.textContent = 'La taille est invalide (valeur attendue: >= 0.5 m).';
                        registerError.style.display = 'block';
                        return;
                    }

                    if (Number.isNaN(age) || age < 1) {
                        event.preventDefault();
                        registerError.textContent = 'L\'age est invalide (valeur attendue: >= 1 an).';
                        registerError.style.display = 'block';
                        return;
                    }

                    if (Number.isNaN(besoins) || besoins < 800) {
                        event.preventDefault();
                        registerError.textContent = 'Les besoins caloriques sont invalides (valeur attendue: >= 800 kcal/jour).';
                        registerError.style.display = 'block';
                        return;
                    }

                    if (motDePasseValeur !== confirmerValeur) {
                        event.preventDefault();
                        passwordError.style.display = 'block';
                        registerError.style.display = 'none';
                        return;
                    }

                    registerError.style.display = 'none';
                    passwordError.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>
