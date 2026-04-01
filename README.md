# 🌱 KOOL HEALTHY – Alimentation durable & nutrition intelligente

<p align="center">
  <img src="screenshots/home.png" alt="KOOL HEALTHY Banner" width="100%" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-OOP-blue?logo=php" />
  <img src="https://img.shields.io/badge/MySQL-Database-orange?logo=mysql" />
  <img src="https://img.shields.io/badge/HTML%2FCSS%2FJS-Frontend-green?logo=html5" />
  <img src="https://img.shields.io/badge/License-MIT-yellow" />
  <img src="https://img.shields.io/badge/PIDEV-Esprit%20Engineering-purple" />
</p>

---

## 📌 À propos du projet

**KOOL HEALTHY** est une plateforme web innovante qui combine intelligence artificielle et conscience écologique pour promouvoir une alimentation saine et durable.

Elle aide les utilisateurs à :

- 🥗 Adopter de meilleures habitudes alimentaires
- 🌍 Réduire leur impact environnemental
- 🤖 Recevoir des recommandations nutritionnelles personnalisées par IA
- ♻️ Lutter contre le gaspillage alimentaire

### 🎯 Problématique

Le projet répond à un double enjeu :

| Enjeu | Description |
|-------|-------------|
| 🏥 **Santé** | Manque de recommandations nutritionnelles adaptées à chaque individu |
| 🌿 **Environnement** | Gaspillage alimentaire et méconnaissance de l'impact écologique des aliments |

---

## ⚙️ Fonctionnalités principales

- 👤 **Gestion des comptes utilisateurs** – Inscription, connexion, profil personnalisé
- 🤖 **Recommandation intelligente de repas** – Propulsée par IA
- 🌱 **Éco-score des aliments** – Affichage de l'impact environnemental
- 📦 **Gestion d'inventaire alimentaire** – Suivi de vos stocks
- ♻️ **Suggestions anti-gaspillage** – Recettes basées sur vos ingrédients disponibles
- 🏆 **Gamification** – Défis écologiques, badges et classement
- 🛠️ **Interface d'administration** – Gestion complète du contenu et des utilisateurs

---

## 📚 Table des matières

- [Installation](#️-installation)
- [Utilisation](#-utilisation)
- [Technologies utilisées](#️-technologies-utilisées)
- [Captures d'écran](#-captures-décran)
- [Contribution](#-contribution)
- [Licence](#-licence)

---

## ⚙️ Installation

### 1. Cloner le repository

```bash
git clone https://github.com/votre-utilisateur/kool-healthy.git
cd kool-healthy
```

### 2. Configurer un serveur local

Utilisez l'un des environnements suivants :

- [XAMPP](https://www.apachefriends.org/)
- [WAMP](https://www.wampserver.com/)
- [MAMP](https://www.mamp.info/)

Placez le projet dans le dossier `htdocs` (ou équivalent).

### 3. Configurer la base de données

1. Ouvrez **phpMyAdmin**
2. Créez une base de données nommée :

```
kool_healthy
```

3. Importez le fichier :

```
database/kool_healthy.sql
```

### 4. Configurer la connexion

Modifiez le fichier `config/db.php` avec vos informations :

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_NAME', 'kool_healthy');
```

### 5. Lancer l'application

Ouvrez votre navigateur et accédez à :

```
http://localhost/kool-healthy
```

---

## 🚀 Utilisation

### 🔓 Visiteur (non connecté)

- Consulter les recettes disponibles
- Voir les éco-scores des aliments
- Explorer les défis écologiques

### 🔐 Utilisateur inscrit

Après connexion, accès à :

- Gestion du profil personnel
- Recommandations de repas personnalisées par IA
- Gestion de l'inventaire alimentaire
- Suggestions anti-gaspillage basées sur l'inventaire
- Participation aux défis écologiques
- Suivi du classement et des badges obtenus

### 🛠️ Administrateur

L'administrateur dispose d'un espace dédié pour :

- Gérer les comptes utilisateurs
- Gérer les aliments et leurs éco-scores
- Créer, modifier et supprimer des défis
- Superviser et modérer le contenu de la plateforme

---

## 🛠️ Technologies utilisées

| Couche | Technologies |
|--------|-------------|
| **Front-end** | HTML5, CSS3, JavaScript |
| **Back-end** | PHP (Programmation Orientée Objet) |
| **Base de données** | MySQL |
| **Intelligence artificielle** | Recommandations basées sur les préférences utilisateur, l'analyse d'inventaire et la saisonnalité |

---

## 📸 Captures d'écran

<p align="center">
  <img src="screenshots/home.png" alt="Page d'accueil" width="48%" />
  <img src="screenshots/dashboard.png" alt="Dashboard utilisateur" width="48%" />
</p>

---

## 🤝 Contribution

Ce projet a été réalisé dans le cadre du cours **PW – 2ème année** à [Esprit School of Engineering](https://esprit.tn/).

### 👥 Équipe

| Membre     | Module              |
|------------|---------------------|
| Étudiant 1 | Gestion utilisateur |
| Étudiant 2 | Recommandation IA   |
| Étudiant 3 | Produits & éco-score|
| Étudiant 4 | Anti-gaspillage     |
| Étudiant 5 | Gamification        |
| Étudiant 6 | Administration      |

### 🔄 Processus de contribution

1. **Fork** le projet
2. **Créer** une branche pour votre fonctionnalité :

```bash
git checkout -b feature/ma-fonctionnalite
```

3. **Committer** vos modifications :

```bash
git commit -m "Ajout d'une fonctionnalité"
```

4. **Pusher** la branche :

```bash
git push origin feature/ma-fonctionnalite
```

5. **Ouvrir** une Pull Request

---

## 📜 Licence

Ce projet est sous licence **MIT**.

Vous êtes autorisé à utiliser, modifier et distribuer ce projet, à condition de conserver la licence originale.

Voir le fichier [`LICENSE`](LICENSE) pour plus d'informations.

---

<p align="center">
  Made with ❤️ by the KOOL HEALTHY Team – Esprit School of Engineering
</p>