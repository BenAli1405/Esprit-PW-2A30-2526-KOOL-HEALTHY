# Guide d'Implémentation du Système KNN

## 📋 Résumé

Ce guide explique comment implémenter le système de recommandation KNN (K-Nearest Neighbors) pour le module Entraînement & Exercice. Le système remplace l'ancien système de règles par un algorithme d'apprentissage automatique simple mais puissant.

---

## 🎯 Étapes d'Installation

### 1️⃣ Exécuter le Script SQL

Copie le contenu de `migration_knn.sql` dans PhpMyAdmin et exécute-le :

```sql
CREATE TABLE exercice_feature (
    id_feature INT AUTO_INCREMENT PRIMARY KEY,
    id_exercice INT NOT NULL UNIQUE,
    intensite_calorique DECIMAL(3,2) CHECK (intensite_calorique BETWEEN 0 AND 1),
    equipement DECIMAL(3,2) CHECK (equipement BETWEEN 0 AND 1),
    difficulte DECIMAL(3,2) CHECK (difficulte BETWEEN 0 AND 1),
    cible_musculaire DECIMAL(3,2) CHECK (cible_musculaire BETWEEN 0 AND 1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_exercice) REFERENCES exercice(id_exercice) ON DELETE CASCADE
);
```

---

### 2️⃣ Configurer la Clé API WorkoutX

**Fichier** : `config/api_keys.php`

1. Visite [workoutxapp.com](https://www.workoutxapp.com)
2. Inscris-toi et obtiens ta clé API (version gratuite disponible)
3. Ouvre `config/api_keys.php` et remplace :
   ```php
   define('WORKOUTX_API_KEY', 'YOUR_API_KEY_HERE');
   ```
   par ta vraie clé API

---

### 3️⃣ Vérifier l'Arborescence des Fichiers

L'implémentation crée les nouveaux fichiers suivants :

```
services/
├── WorkoutXApiService.php          ✅ Service API
models/
├── KnnModel.php                    ✅ Algorithme KNN
views/
├── front/exercices/
│   └── recommander_knn.php         ✅ Vue front
├── back/exercices/
│   ├── features_list.php           ✅ Liste admin
│   └── features_form.php           ✅ Formulaire admin
config/
├── api_keys.php                    ✅ Config API
migrations/
├── migration_knn.sql               ✅ Script SQL
```

Et modifie les fichiers existants :
- `controllers/ExerciceController.php` → méthode `recommanderKnn()`
- `controllers/AdminController.php` → méthodes `listFeatures()`, `editFeature()`, `importFeature()`
- `index.php` → routes KNN

---

## 🚀 Utilisation

### Front Office - Recommandation KNN

**URL** : `index.php?action=recommander_knn`

1. Sélectionne un exercice dans le dropdown
2. Clique sur "Trouver les exercices similaires"
3. L'algorithme KNN calcule la distance euclidienne et affiche les 3 exercices les plus proches

**Exemple** :
```
Exercice source : "Pompes"
Distance : 0.1523 → 84% similitude ✅
Distance : 0.2456 → 75% similitude
Distance : 0.3789 → 62% similitude
```

---

### Back Office - Gestion des Features

#### Liste des Features

**URL** : `index.php?action=admin_features`

Affiche tous les exercices avec leurs features actuelles ou un badge "Non renseigné".

#### Modifier les Features Manuellement

**URL** : `index.php?action=admin_edit_feature&id=3`

Formulaire avec 4 champs décimaux (0-1) :
- **Intensité Calorique** : Effort énergétique de l'exercice (0=léger, 1=intense)
- **Équipement** : Type d'équipement utilisé (0=poids du corps, 1=lourd)
- **Difficulté** : Complexité technique (0=facile, 1=difficile)
- **Cible Musculaire** : Groupe musculaire principal (0=stabilisateurs, 1=grands groupes)

**Guide de Normalisation** :

| Domaine | 0.0-0.3 | 0.4-0.6 | 0.7-1.0 |
|---------|---------|---------|---------|
| **Intensité** | Faible (étirements) | Modéré (fentes) | Haute (burpees) |
| **Équipement** | Poids du corps | Léger (haltères) | Lourd (barres) |
| **Difficulté** | Débutant | Intermédiaire | Avancé |
| **Cible** | Petit groupe (core) | Groupe moyen (bras) | Grand groupe (poitrine) |

#### Importer depuis WorkoutX API

**URL** : `index.php?action=admin_import_feature&id=3`

Action :
1. Récupère les données de l'API WorkoutX pour cet exercice
2. Normalise automatiquement les 4 features
3. Insère ou met à jour dans la base de données

**Important** : Les données brutes de l'API sont normalisées selon des règles prédéfinies.

---

## 🧮 Algorithme KNN Expliqué

### Distance Euclidienne (4D)

Pour chaque exercice, on calcule la distance par rapport à l'exercice source :

```
distance = √((I1-I2)² + (E1-E2)² + (D1-D2)² + (C1-C2)²)

Où :
- I = intensité calorique
- E = équipement
- D = difficulté
- C = cible musculaire
```

### Exemple Calculé

**Source** : "Pompes" (0.7, 0.1, 0.6, 0.8)

Comparaison avec "Gainage" (0.65, 0.1, 0.5, 0.6) :

```
distance = √((0.7-0.65)² + (0.1-0.1)² + (0.6-0.5)² + (0.8-0.6)²)
         = √(0.0025 + 0 + 0.01 + 0.04)
         = √0.0525
         = 0.229
```

**Similarité** = 100 - (0.229 × 100) = **77.1%**

---

## ⚙️ Configuration Avancée

### Changer le nombre de voisins (K)

Dans `ExerciceController::recommanderKnn()` :

```php
// Changer de 3 à 5
$similarExercises = $this->knnModel->getSimilarExercises($selectedExerciceId, 5);
```

### Normalisation Personnalisée des Features API

Modifie les méthodes dans `services/WorkoutXApiService.php` :
- `normalizeIntensity()` → Règles d'intensité
- `normalizeEquipment()` → Catégories d'équipement
- `normalizeDifficulty()` → Niveaux de difficulté
- `normalizeBodyPart()` → Groupes musculaires

---

## 🐛 Débogage

### Exercice n'a pas de features

Si le KNN retourne "n'a pas de caractéristiques renseignées" :
→ Va dans Admin Features et ajoute/importe les features

### API WorkoutX ne répond pas

Vérifier dans `config/api_keys.php` :
- La clé API est correcte
- La clé n'est pas expirée
- La connexion internet fonctionne

### Distance nulle (même exercice)

C'est normal ! Les exercices avec exactement les mêmes features retournent distance = 0.

---

## 📊 Données de Test

Le script `migration_knn.sql` insère des données exemple :

```
Étirements dynamiques (1)  → [0.2, 0.1, 0.1, 0.3]
Fentes avant (2)          → [0.6, 0.2, 0.5, 0.7]
Pompes (3)                → [0.7, 0.1, 0.6, 0.8]
Gainage (4)               → [0.65, 0.1, 0.5, 0.6]
```

Teste avec : "Sélectionne Pompes → Trouve Gainage comme plus proche"

---

## 🔒 Sécurité

✅ **Implémentation sécurisée** :
- PDO avec prepared statements (protection SQL injection)
- Validation PHP (pas de HTML5, comme demandé)
- Validation des valeurs entre 0 et 1
- Pas de stockage des clés API en commit (utilise `.gitignore`)

⚠️ **À faire en production** :
```php
// Ne pas commiter config/api_keys.php
// Utiliser des variables d'environnement :
define('WORKOUTX_API_KEY', $_ENV['WORKOUTX_API_KEY']);
```

---

## 📝 Notes Importantes

1. **Ancien système** : Les routes `recommander_ia` et la table `recommandation_regle` restent intactes pour compatibilité
2. **Base de données** : Table `exercice_feature` + contraintes CHECK (norme SQL)
3. **POO** : Classes distinctes Service/Model/Controller
4. **Code commenté** : Toutes les méthodes KNN expliquées ligne par ligne

---

## 🎓 Pour Aller Plus Loin

Améliorations possibles :

- [ ] Pondérer les dimensions (ex: intensité = 50%, autre = 30%, 10%, 10%)
- [ ] Utiliser distance de Minkowski ou Manhattan
- [ ] Cache Redis des résultats KNN
- [ ] UI interactive (slider pour ajuster K)
- [ ] Export CSV des features
- [ ] Batch import API pour tous les exercices

---

**Créé le** : 3 mai 2026  
**Version** : 1.0  
**Architecture** : MVC + PDO + POO
