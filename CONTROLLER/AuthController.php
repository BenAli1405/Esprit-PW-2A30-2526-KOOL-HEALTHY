<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/Utilisateur.php';
require_once __DIR__ . '/../MODEL/ProfilNutritif.php';

class AuthController
{
    private function configValue($key, $default = '')
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        $fallbackMap = [
            'GOOGLE_CLIENT_ID' => 'getGoogleClientId',
            'GOOGLE_CLIENT_SECRET' => 'getGoogleClientSecret',
            'GOOGLE_REDIRECT_URI' => 'getGoogleRedirectUri',
            'MAIL_FROM' => 'getMailFrom'
        ];

        $method = $fallbackMap[$key] ?? null;
        if ($method !== null && method_exists(config::class, $method)) {
            $fallback = (string) call_user_func([config::class, $method]);
            if ($fallback !== '') {
                return $fallback;
            }
        }

        return $default;
    }

    private function urlActionAbsolue($action)
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $self = $_SERVER['PHP_SELF'] ?? '/CONTROLLER/AuthController.php';
        return $scheme . '://' . $host . $self . '?action=' . rawurlencode($action);
    }

    private function getGoogleOAuthConfig()
    {
        $clientId = trim((string) $this->configValue('GOOGLE_CLIENT_ID'));
        $clientSecret = trim((string) $this->configValue('GOOGLE_CLIENT_SECRET'));
        $redirectUri = trim((string) $this->configValue('GOOGLE_REDIRECT_URI'));

        if ($redirectUri === '') {
            $redirectUri = $this->urlActionAbsolue('google_callback');
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri
        ];
    }

    private function requetePostForm($url, $data)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'ok' => $response !== false && $status >= 200 && $status < 300,
                'status' => $status,
                'body' => $response !== false ? (string) $response : '',
                'error' => $error
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data),
                'timeout' => 20,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        $status = 0;
        if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $status = (int) $matches[1];
        }

        return [
            'ok' => $response !== false && $status >= 200 && $status < 300,
            'status' => $status,
            'body' => $response !== false ? (string) $response : '',
            'error' => ''
        ];
    }

    private function requeteGetJson($url, $headers = [])
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || $status < 200 || $status >= 300) {
                return null;
            }

            $decoded = json_decode((string) $response, true);
            return is_array($decoded) ? $decoded : null;
        }

        $headerText = "";
        if (!empty($headers)) {
            $headerText = implode("\r\n", $headers) . "\r\n";
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headerText,
                'timeout' => 20,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function chargerUtilisateurParId($id)
    {
        $db = config::getConnexion();
        $req = $db->prepare("SELECT u.*, p.age, p.allergies, p.besoins_caloriques
                             FROM utilisateurs u
                             LEFT JOIN profil_nutritif p ON p.id = u.profil
                             WHERE u.id = :id
                             LIMIT 1");
        $req->execute(['id' => (int) $id]);
        return $req->fetch() ?: null;
    }

    private function chargerUtilisateurParEmail($email)
    {
        $db = config::getConnexion();
        $req = $db->prepare("SELECT u.*, p.age, p.allergies, p.besoins_caloriques
                             FROM utilisateurs u
                             LEFT JOIN profil_nutritif p ON p.id = u.profil
                             WHERE u.email = :email
                             LIMIT 1");
        $req->execute(['email' => $email]);
        return $req->fetch() ?: null;
    }

    private function ouvrirSessionUtilisateur($utilisateur)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['utilisateur'] = [
            'id' => $utilisateur['id'],
            'nom' => $utilisateur['nom'],
            'email' => $utilisateur['email'],
            'role' => $utilisateur['role'],
            'poids' => $utilisateur['poids'],
            'taille' => $utilisateur['taille'],
            'imc' => $utilisateur['imc'],
            'age' => $utilisateur['age'],
            'allergies' => $utilisateur['allergies'],
            'besoins_caloriques' => $utilisateur['besoins_caloriques']
        ];
    }

    private function genererNomUniqueDepuisEmail($email, $db)
    {
        $base = strtolower((string) strstr($email, '@', true));
        $base = preg_replace('/[^a-z0-9_]/', '_', $base);
        $base = trim((string) $base, '_');
        if ($base === '') {
            $base = 'user_google';
        }

        $nom = $base;
        $suffixe = 0;
        $check = $db->prepare('SELECT id FROM utilisateurs WHERE nom = :nom LIMIT 1');
        while (true) {
            $check->execute(['nom' => $nom]);
            if (!$check->fetch()) {
                return $nom;
            }
            $suffixe++;
            $nom = $base . '_' . $suffixe;
        }
    }

    private function creerUtilisateurGoogle($nomGoogle, $email)
    {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            $nomUnique = $this->genererNomUniqueDepuisEmail($email, $db);
            if ($nomGoogle !== '') {
                $nomGoogleNormalise = preg_replace('/\s+/', '_', strtolower(trim($nomGoogle)));
                $nomGoogleNormalise = preg_replace('/[^a-z0-9_]/', '_', (string) $nomGoogleNormalise);
                $nomGoogleNormalise = trim((string) $nomGoogleNormalise, '_');
                if ($nomGoogleNormalise !== '') {
                    $nomUnique = $this->genererNomUniqueDepuisEmail($nomGoogleNormalise . '@google.local', $db);
                }
            }

            $motDePasseTechnique = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            $insertUser = $db->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, poids, taille, imc, created_at)
                                        VALUES (:nom, :email, :mot_de_passe, :role, :poids, :taille, :imc, NOW())");
            $insertUser->execute([
                'nom' => $nomUnique,
                'email' => $email,
                'mot_de_passe' => $motDePasseTechnique,
                'role' => 'utilisateur',
                'poids' => null,
                'taille' => null,
                'imc' => null
            ]);

            $userId = (int) $db->lastInsertId();

            $insertProfil = $db->prepare("INSERT INTO profil_nutritif (utilisateur, age, allergies, besoins_caloriques)
                                          VALUES (:utilisateur, :age, :allergies, :besoins)");
            $insertProfil->execute([
                'utilisateur' => $userId,
                'age' => 18,
                'allergies' => '',
                'besoins' => 2000
            ]);

            $profilId = (int) $db->lastInsertId();
            $updateUser = $db->prepare('UPDATE utilisateurs SET profil = :profil WHERE id = :id');
            $updateUser->execute([
                'profil' => $profilId,
                'id' => $userId
            ]);

            $db->commit();
            return $this->chargerUtilisateurParId($userId);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return null;
        }
    }

    private function initialiserTableResetCodes()
    {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS password_reset_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_used (utilisateur_id, used),
            INDEX idx_expires (expires_at),
            CONSTRAINT fk_reset_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function envoyerCodeResetEmail($email, $code)
    {
        $from = trim((string) $this->configValue('MAIL_FROM', 'no-reply@koolhealthy.local'));
        $subject = 'Kool Healthy - Code de reinitialisation';
        $message = "Bonjour,\n\n";
        $message .= "Voici votre code de reinitialisation: " . $code . "\n";
        $message .= "Ce code expire dans 15 minutes.\n\n";
        $message .= "Si vous n'etes pas a l'origine de cette demande, ignorez cet email.\n";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: Kool Healthy <" . $from . ">\r\n";

        return @mail($email, $subject, $message, $headers);
    }

    private function normaliserRole($role)
    {
        return strtolower(trim((string) $role));
    }

    private function colonneExiste($db, $table, $colonne)
    {
        $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :colonne");
        $stmt->execute(['colonne' => $colonne]);
        return $stmt->fetchColumn() !== false;
    }

    private function calculerImc($poids, $taille)
    {
        $poids = is_numeric($poids) ? (float) $poids : null;
        $tailleM = is_numeric($taille) ? (float) $taille : null;

        if ($poids === null || $tailleM === null) {
            return null;
        }

        if ($tailleM > 3) {
            $tailleM = $tailleM / 100;
        }

        if ($tailleM <= 0 || $poids <= 0) {
            return null;
        }

        return round($poids / ($tailleM * $tailleM), 2);
    }

    public function inscrire($utilisateur, $profilNutritif)
    {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = :email LIMIT 1");
            $check->execute(['email' => $utilisateur->getEmail()]);

            if ($check->fetch()) {
                $db->rollBack();
                return false;
            }

            $checkNom = $db->prepare("SELECT id FROM utilisateurs WHERE nom = :nom LIMIT 1");
            $checkNom->execute(['nom' => $utilisateur->getNom()]);

            if ($checkNom->fetch()) {
                $db->rollBack();
                return false;
            }

                $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, poids, taille, imc, created_at)
                    VALUES (:nom, :email, :mot_de_passe, :role, :poids, :taille, :imc, :created_at)";
            $req = $db->prepare($sql);
            $req->execute([
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
                'mot_de_passe' => password_hash($utilisateur->getMotDePasse(), PASSWORD_DEFAULT),
                'role' => $utilisateur->getRole(),
                'poids' => $utilisateur->getPoids(),
                'taille' => $utilisateur->getTaille(),
                'imc' => $utilisateur->getImc(),
                'created_at' => $utilisateur->getCreatedAt()
            ]);

            $utilisateurId = (int) $db->lastInsertId();

            $profilSql = "INSERT INTO profil_nutritif (utilisateur, age, allergies, besoins_caloriques)
                          VALUES (:utilisateur, :age, :allergies, :besoins_caloriques)";
            $profilReq = $db->prepare($profilSql);
            $profilReq->execute([
                'utilisateur' => $utilisateurId,
                'age' => $profilNutritif->getAge(),
                'allergies' => $profilNutritif->getAllergies(),
                'besoins_caloriques' => $profilNutritif->getBesoinsCaloriques()
            ]);

            $profilId = (int) $db->lastInsertId();
            $updateLiaison = $db->prepare("UPDATE utilisateurs SET profil = :profil WHERE id = :id");
            $updateLiaison->execute([
                'profil' => $profilId,
                'id' => $utilisateurId
            ]);

            $db->commit();
            return $utilisateurId;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die('Erreur inscription: ' . $e->getMessage());
        }
    }

    public function urlConnexionGoogle()
    {
        $cfg = $this->getGoogleOAuthConfig();
        if (($cfg['client_id'] ?? '') === '' || ($cfg['client_secret'] ?? '') === '') {
            return null;
        }

        $params = [
            'client_id' => $cfg['client_id'],
            'redirect_uri' => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function connecterViaGoogle($code)
    {
        $code = trim((string) $code);
        if ($code === '') {
            return ['success' => false, 'error' => 'google_code_missing'];
        }

        $cfg = $this->getGoogleOAuthConfig();
        if (($cfg['client_id'] ?? '') === '' || ($cfg['client_secret'] ?? '') === '') {
            return ['success' => false, 'error' => 'google_not_configured'];
        }

        $tokenResponse = $this->requetePostForm('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'redirect_uri' => $cfg['redirect_uri'],
            'grant_type' => 'authorization_code'
        ]);

        if (!($tokenResponse['ok'] ?? false)) {
            return ['success' => false, 'error' => 'google_exchange_failed'];
        }

        $tokenData = json_decode((string) ($tokenResponse['body'] ?? ''), true);
        if (!is_array($tokenData) || empty($tokenData['access_token'])) {
            return ['success' => false, 'error' => 'google_exchange_failed'];
        }

        $profileData = $this->requeteGetJson('https://www.googleapis.com/oauth2/v3/userinfo', [
            'Authorization: Bearer ' . $tokenData['access_token']
        ]);

        if (!is_array($profileData)) {
            return ['success' => false, 'error' => 'google_profile_failed'];
        }

        $email = trim((string) ($profileData['email'] ?? ''));
        $nom = trim((string) ($profileData['name'] ?? ''));

        if ($email === '') {
            return ['success' => false, 'error' => 'google_email_missing'];
        }

        $utilisateur = $this->chargerUtilisateurParEmail($email);
        if (!$utilisateur) {
            $utilisateur = $this->creerUtilisateurGoogle($nom, $email);
        }

        if (!$utilisateur) {
            return ['success' => false, 'error' => 'google_user_failed'];
        }

        $this->ouvrirSessionUtilisateur($utilisateur);
        return ['success' => true, 'user' => $utilisateur];
    }

    public function demanderReinitialisationMotDePasse($email)
    {
        $email = trim((string) $email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'invalid_email'];
        }

        $utilisateur = $this->chargerUtilisateurParEmail($email);
        if (!$utilisateur) {
            return ['success' => false, 'error' => 'email_not_found'];
        }

        $db = config::getConnexion();
        try {
            $this->initialiserTableResetCodes();
            $db->beginTransaction();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $codeHash = password_hash($code, PASSWORD_DEFAULT);
            $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60));

            $disableOld = $db->prepare('UPDATE password_reset_codes SET used = 1 WHERE utilisateur_id = :uid AND used = 0');
            $disableOld->execute(['uid' => (int) $utilisateur['id']]);

            $insertCode = $db->prepare('INSERT INTO password_reset_codes (utilisateur_id, code_hash, expires_at, used) VALUES (:uid, :code_hash, :expires_at, 0)');
            $insertCode->execute([
                'uid' => (int) $utilisateur['id'],
                'code_hash' => $codeHash,
                'expires_at' => $expiresAt
            ]);

            if (!$this->envoyerCodeResetEmail($email, $code)) {
                $db->rollBack();
                return ['success' => false, 'error' => 'email_send_failed'];
            }

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return ['success' => false, 'error' => 'server_error'];
        }
    }

    public function reinitialiserMotDePasseParCode($email, $code, $nouveauMotDePasse)
    {
        $email = trim((string) $email);
        $code = trim((string) $code);
        $nouveauMotDePasse = (string) $nouveauMotDePasse;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'invalid_email'];
        }

        if ($code === '') {
            return ['success' => false, 'error' => 'invalid_code'];
        }

        if (strlen($nouveauMotDePasse) < 6) {
            return ['success' => false, 'error' => 'password_too_short'];
        }

        $utilisateur = $this->chargerUtilisateurParEmail($email);
        if (!$utilisateur) {
            return ['success' => false, 'error' => 'invalid_code'];
        }

        $db = config::getConnexion();
        try {
            $this->initialiserTableResetCodes();
            $db->beginTransaction();

            $codeReq = $db->prepare('SELECT id, code_hash, expires_at FROM password_reset_codes WHERE utilisateur_id = :uid AND used = 0 ORDER BY id DESC LIMIT 1');
            $codeReq->execute(['uid' => (int) $utilisateur['id']]);
            $codeData = $codeReq->fetch();

            if (!$codeData) {
                $db->rollBack();
                return ['success' => false, 'error' => 'invalid_code'];
            }

            if (strtotime((string) $codeData['expires_at']) < time()) {
                $expireReq = $db->prepare('UPDATE password_reset_codes SET used = 1 WHERE id = :id');
                $expireReq->execute(['id' => (int) $codeData['id']]);
                $db->commit();
                return ['success' => false, 'error' => 'code_expired'];
            }

            if (!password_verify($code, (string) $codeData['code_hash'])) {
                $db->rollBack();
                return ['success' => false, 'error' => 'invalid_code'];
            }

            $updateUser = $db->prepare('UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE id = :id');
            $updateUser->execute([
                'mot_de_passe' => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT),
                'id' => (int) $utilisateur['id']
            ]);

            $useCode = $db->prepare('UPDATE password_reset_codes SET used = 1 WHERE id = :id');
            $useCode->execute(['id' => (int) $codeData['id']]);

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return ['success' => false, 'error' => 'server_error'];
        }
    }

        public function connecter($identifiant, $mot_de_passe)
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT u.*, p.age, p.allergies, p.besoins_caloriques
                    FROM utilisateurs u
                    LEFT JOIN profil_nutritif p ON p.id = u.profil
                WHERE u.nom = :identifiant OR u.email = :identifiant
                    LIMIT 1";
            $req = $db->prepare($sql);
            $req->execute(['identifiant' => $identifiant]);
            $utilisateur = $req->fetch();

            if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                $this->ouvrirSessionUtilisateur($utilisateur);
                return true;
            }

            return false;
        } catch (Exception $e) {
            die('Erreur connexion: ' . $e->getMessage());
        }
    }

    public function deconnecter()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function utilisateurConnecte()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['utilisateur'] ?? null;
    }

    public function estAdmin($utilisateur = null)
    {
        if ($utilisateur === null) {
            $utilisateur = $this->utilisateurConnecte();
        }

        if (!$utilisateur) {
            return false;
        }

        return $this->normaliserRole($utilisateur['role'] ?? '') === 'admin';
    }

    public function exigerAdmin($redirectSiInvite, $redirectSiInterdit)
    {
        $utilisateur = $this->utilisateurConnecte();
        if (!$utilisateur) {
            header('Location: ' . $redirectSiInvite);
            exit();
        }

        if (!$this->estAdmin($utilisateur)) {
            header('Location: ' . $redirectSiInterdit);
            exit();
        }

        return $utilisateur;
    }

    public function exigerFront($redirectSiAdmin)
    {
        $utilisateur = $this->utilisateurConnecte();
        if ($this->estAdmin($utilisateur)) {
            header('Location: ' . $redirectSiAdmin);
            exit();
        }

        return $utilisateur;
    }

    public function mettreAJourProfil($userId, $donnees)
    {
        $db = config::getConnexion();

        $nom = trim((string) ($donnees['nom'] ?? ''));
        $email = trim((string) ($donnees['email'] ?? ''));
        $poids = ($donnees['poids'] ?? '') !== '' ? (float) $donnees['poids'] : null;
        $taille = ($donnees['taille'] ?? '') !== '' ? (float) $donnees['taille'] : null;
        $age = isset($donnees['age']) ? (int) $donnees['age'] : 0;
        $nouveauMotDePasse = (string) ($donnees['nouveau_mot_de_passe'] ?? '');

        if ($nom === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $age <= 0) {
            return ['success' => false, 'error' => 'invalid_data'];
        }

        if ($poids !== null && $poids <= 0) {
            return ['success' => false, 'error' => 'invalid_data'];
        }

        if ($taille !== null && $taille <= 0) {
            return ['success' => false, 'error' => 'invalid_data'];
        }

        if ($nouveauMotDePasse !== '' && strlen($nouveauMotDePasse) < 6) {
            return ['success' => false, 'error' => 'password_too_short'];
        }

        try {
            $db->beginTransaction();

            $checkEmail = $db->prepare("SELECT id FROM utilisateurs WHERE email = :email AND id <> :id LIMIT 1");
            $checkEmail->execute([
                'email' => $email,
                'id' => $userId
            ]);

            if ($checkEmail->fetch()) {
                $db->rollBack();
                return ['success' => false, 'error' => 'email_exists'];
            }

            $checkNom = $db->prepare("SELECT id FROM utilisateurs WHERE nom = :nom AND id <> :id LIMIT 1");
            $checkNom->execute([
                'nom' => $nom,
                'id' => $userId
            ]);

            if ($checkNom->fetch()) {
                $db->rollBack();
                return ['success' => false, 'error' => 'name_exists'];
            }

            $imc = $this->calculerImc($poids, $taille);
            $aObjectif = $this->colonneExiste($db, 'utilisateurs', 'objectif');

            $sql = "UPDATE utilisateurs SET nom = :nom, email = :email, poids = :poids, taille = :taille, imc = :imc";
            if ($aObjectif) {
                $sql .= ", objectif = :objectif";
            }
            if ($nouveauMotDePasse !== '') {
                $sql .= ", mot_de_passe = :mot_de_passe";
            }
            $sql .= " WHERE id = :id";

            $params = [
                'nom' => $nom,
                'email' => $email,
                'poids' => $poids,
                'taille' => $taille,
                'imc' => $imc,
                'id' => $userId
            ];

            if ($aObjectif) {
                $params['objectif'] = trim((string) ($donnees['objectif'] ?? ''));
            }

            if ($nouveauMotDePasse !== '') {
                $params['mot_de_passe'] = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
            }

            $updateUser = $db->prepare($sql);
            $updateUser->execute($params);

            $profilLie = $db->prepare("SELECT profil FROM utilisateurs WHERE id = :id LIMIT 1");
            $profilLie->execute(['id' => $userId]);
            $profilId = (int) ($profilLie->fetchColumn() ?: 0);

            if ($profilId <= 0) {
                $fallbackProfil = $db->prepare("SELECT id FROM profil_nutritif WHERE utilisateur = :utilisateur LIMIT 1");
                $fallbackProfil->execute(['utilisateur' => $userId]);
                $profilId = (int) ($fallbackProfil->fetchColumn() ?: 0);

                if ($profilId > 0) {
                    $updateLiaison = $db->prepare("UPDATE utilisateurs SET profil = :profil WHERE id = :id");
                    $updateLiaison->execute([
                        'profil' => $profilId,
                        'id' => $userId
                    ]);
                }
            }

            if ($profilId > 0) {
                $updateProfil = $db->prepare("UPDATE profil_nutritif SET age = :age WHERE id = :id");
                $updateProfil->execute([
                    'age' => $age,
                    'id' => $profilId
                ]);
            } else {
                $insertProfil = $db->prepare("INSERT INTO profil_nutritif (utilisateur, age, allergies, besoins_caloriques)
                                             VALUES (:utilisateur, :age, :allergies, :besoins_caloriques)");
                $insertProfil->execute([
                    'utilisateur' => $userId,
                    'age' => $age,
                    'allergies' => '',
                    'besoins_caloriques' => 0
                ]);

                $profilId = (int) $db->lastInsertId();
                $updateLiaison = $db->prepare("UPDATE utilisateurs SET profil = :profil WHERE id = :id");
                $updateLiaison->execute([
                    'profil' => $profilId,
                    'id' => $userId
                ]);
            }

            $reloadUser = $db->prepare("SELECT u.*, p.age, p.allergies, p.besoins_caloriques
                                        FROM utilisateurs u
                                        LEFT JOIN profil_nutritif p ON p.id = u.profil
                                        WHERE u.id = :id
                                        LIMIT 1");
            $reloadUser->execute(['id' => $userId]);
            $utilisateur = $reloadUser->fetch();

            if ($utilisateur) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['utilisateur'] = [
                    'id' => $utilisateur['id'],
                    'nom' => $utilisateur['nom'],
                    'email' => $utilisateur['email'],
                    'role' => $utilisateur['role'],
                    'poids' => $utilisateur['poids'],
                    'taille' => $utilisateur['taille'],
                    'imc' => $utilisateur['imc'],
                    'age' => $utilisateur['age'],
                    'allergies' => $utilisateur['allergies'],
                    'besoins_caloriques' => $utilisateur['besoins_caloriques'],
                    'objectif' => $utilisateur['objectif'] ?? null
                ];
            }

            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'error' => 'server_error'];
        }
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $controller = new AuthController();
    $action = $_GET['action'] ?? '';

    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
        $role = 'utilisateur';
        $poids = (float) ($_POST['poids'] ?? 0);
        $taille = (float) ($_POST['taille'] ?? 0);
        $age = (int) ($_POST['age'] ?? 0);
        $allergies = trim($_POST['allergies'] ?? '');
        $besoins_caloriques = (int) ($_POST['besoins_caloriques'] ?? 0);

        if ($mot_de_passe !== $confirmer_mot_de_passe) {
            header('Location: ../VIEW/register.php?error=password_mismatch');
            exit();
        }

        $utilisateur = new Utilisateur(
            $nom,
            $email,
            $mot_de_passe,
            $role,
            $poids,
            $taille,
            null
        );
        $profilNutritif = new ProfilNutritif(null, $age, $allergies, $besoins_caloriques);
        $resultat = $controller->inscrire($utilisateur, $profilNutritif);

        if ($resultat === false) {
            header('Location: ../VIEW/register.php?error=register');
            exit();
        }

        header('Location: ../VIEW/auth.php?success=register');
        exit();
    }

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $identifiant = trim($_POST['nom'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';

        if ($controller->connecter($identifiant, $mot_de_passe)) {
            $utilisateurConnecte = $controller->utilisateurConnecte();
            if ($controller->estAdmin($utilisateurConnecte)) {
                header('Location: ../VIEW/backoffice.php');
            } else {
                header('Location: ../VIEW/home.php');
            }
            exit();
        }

        header('Location: ../VIEW/auth.php?error=login');
        exit();
    }

    if ($action === 'google_login') {
        $urlGoogle = $controller->urlConnexionGoogle();
        if ($urlGoogle === null) {
            header('Location: ../VIEW/auth.php?error=google_not_configured');
            exit();
        }

        header('Location: ' . $urlGoogle);
        exit();
    }

    if ($action === 'google_callback') {
        $code = $_GET['code'] ?? '';
        $result = $controller->connecterViaGoogle($code);

        if (!($result['success'] ?? false)) {
            $errorCode = urlencode((string) ($result['error'] ?? 'google_login'));
            header('Location: ../VIEW/auth.php?error=' . $errorCode);
            exit();
        }

        $utilisateurConnecte = $controller->utilisateurConnecte();
        if ($controller->estAdmin($utilisateurConnecte)) {
            header('Location: ../VIEW/backoffice.php');
        } else {
            header('Location: ../VIEW/home.php');
        }
        exit();
    }

    if ($action === 'logout') {
        $controller->deconnecter();
        header('Location: ../VIEW/auth.php');
        exit();
    }

    if ($action === 'request_password_reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $resultat = $controller->demanderReinitialisationMotDePasse($email);

        if (!($resultat['success'] ?? false)) {
            $code = urlencode((string) ($resultat['error'] ?? 'reset_request_failed'));
            header('Location: ../VIEW/forgot-password.php?error=' . $code);
            exit();
        }

        header('Location: ../VIEW/forgot-password.php?success=code_sent&email=' . urlencode($email));
        exit();
    }

    if ($action === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $code = trim((string) ($_POST['code'] ?? ''));
        $nouveauMotDePasse = (string) ($_POST['nouveau_mot_de_passe'] ?? '');
        $confirmer = (string) ($_POST['confirmer_mot_de_passe'] ?? '');

        if ($nouveauMotDePasse !== $confirmer) {
            header('Location: ../VIEW/forgot-password.php?error=password_mismatch');
            exit();
        }

        $resultat = $controller->reinitialiserMotDePasseParCode($email, $code, $nouveauMotDePasse);
        if (!($resultat['success'] ?? false)) {
            $codeErreur = urlencode((string) ($resultat['error'] ?? 'reset_failed'));
            header('Location: ../VIEW/forgot-password.php?error=' . $codeErreur);
            exit();
        }

        header('Location: ../VIEW/auth.php?success=password_reset');
        exit();
    }

    if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $utilisateurConnecte = $controller->utilisateurConnecte();
        if (!$utilisateurConnecte) {
            header('Location: ../VIEW/auth.php');
            exit();
        }

        $resultat = $controller->mettreAJourProfil((int) $utilisateurConnecte['id'], $_POST);

        if (!($resultat['success'] ?? false)) {
            $code = $resultat['error'] ?? 'profile_update';
            header('Location: ../VIEW/profil.php?error=' . urlencode($code));
            exit();
        }

        header('Location: ../VIEW/profil.php?success=profile_updated');
        exit();
    }

    header('Location: ../VIEW/auth.php');
    exit();
}
?>
