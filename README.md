# Kool Healthy — Module Gamification (MVC PHP)

Architecture identique au projet **Recettes** : MODEL / VIEW / CONTROLLER / CSS / JS / assets.

## Structure

```
Gamification_MVC/
├── config.php                          ← Connexion PDO + création automatique des tables
├── gamification_db.sql                 ← Script SQL (tables + données exemples)
├── MODEL/
│   ├── Defi.php                        ← Entité Défi
│   ├── Participation.php               ← Entité Participation
│   ├── Utilisateur.php                 ← (partagé avec Recettes)
│   └── ProfilNutritif.php              ← (partagé avec Recettes)
├── CONTROLLER/
│   ├── DefiController.php              ← CRUD défis + routage HTTP
│   ├── ParticipationController.php     ← Participation + classement
├── VIEW/
│   ├── gamification.php               ← Front-office (défis, classement)
│   └── backoffice-gamification.php    ← Back-office admin
├── CSS/
│   └── gamification.css               ← Variables Kool Healthy identiques à styles.css
├── JS/
│   └── gamification.js                ← Navigation onglets, animations
└── assets/                            ← Logo Kool Healthy (partagé)
```

## Base de données

Le module partage la base `recettes_db` avec le projet Recettes.  
Il ajoute 2 tables : `defis`, `participations`.

**Auto-création** : `config.php` crée automatiquement les tables au premier accès.  
**Manuel** : importer `gamification_db.sql` via phpMyAdmin ou CLI :
```bash
mysql -u root -p recettes_db < gamification_db.sql
```

## Accès

| Page | URL |
|------|-----|
| Page | URL |
|------|-----|
| Front-office | `VIEW/gamification.php` |
| Back-office | `VIEW/backoffice-gamification.php` |

> Accédez à `http://localhost/htdocs/Gamification/VIEW/gamification.php` pour le front-office et `http://localhost/htdocs/Gamification/VIEW/backoffice-gamification.php` pour le back-office.

> L’authentification a été retirée. Les pages PHP sont maintenant les points d’entrée principaux.

## Design

Même tokens CSS que le projet Recettes :
- `--vert-kool` #4CAF50
- `--bleu-tech` #29B6F6
- Police Inter
- Composants : `.panel`, `.btn`, `.topbar`, `.footer`
