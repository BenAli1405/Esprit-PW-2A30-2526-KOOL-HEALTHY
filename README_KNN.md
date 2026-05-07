# ⚡ QUICK START - Démarrage Rapide

## 5 Minutes Chrono ⏱️

### 1️⃣ Installer la Base de Données (1 min)

```bash
PhpMyAdmin → SQL → Coller migration_knn.sql → Execute
```

**Vérification** :
```sql
SELECT COUNT(*) FROM exercice_feature;
-- ✅ Doit retourner 4
```

---

### 2️⃣ Configurer l'API (1 min)

**Fichier** : `config/api_keys.php`

```php
define('WORKOUTX_API_KEY', 'VOTRE_CLE_ICI');
```

👉 Obtenir clé : https://www.workoutxapp.com (5 min, gratuit)

---

### 3️⃣ Copier les Fichiers (2 min)

```
✅ services/WorkoutXApiService.php
✅ models/KnnModel.php
✅ views/front/exercices/recommander_knn.php
✅ views/back/exercices/features_list.php
✅ views/back/exercices/features_form.php
✅ config/api_keys.php
```

---

### 4️⃣ Vérifier l'Installation (1 min)

```
Aller à : http://localhost/kool_healthy3/?action=recommander_knn

✅ Si page charge → Installation OK
❌ Si erreur 404 → Vérifier routes dans index.php
```

---

## Utilisation 🎯

### Front Office
```
http://localhost/kool_healthy3/?action=recommander_knn

1. Sélectionner un exercice
2. Cliquer "Trouver les exercices similaires"
3. Voir les résultats KNN (distance, similarité %)
```

### Back Office
```
http://localhost/kool_healthy3/?action=admin_features

1. Voir tous les exercices + leurs features
2. Cliquer "Modifier" pour éditer les 4 valeurs (0-1)
3. Cliquer "Importer API" pour récupérer depuis WorkoutX
```

---

## Structure Créée 📁

```
Avant : ❌ recommandation_regle (règles fixes)
Après  : ✅ exercice_feature + KNN (dynamique)

Algorithme : Distance euclidienne 4D
Distance = √((I₁-I₂)² + (E₁-E₂)² + (D₁-D₂)² + (C₁-C₂)²)

Où I, E, D, C ∈ [0, 1]
```

---

## 📚 Documentation Complète

| Fichier | Objectif |
|---------|----------|
| **KNN_IMPLEMENTATION_GUIDE.md** | Tous les détails |
| **VERIFICATION_CHECKLIST.md** | 50+ tests avant prod |
| **TROUBLESHOOTING.md** | 15+ problèmes + solutions |
| **HEADER_INTEGRATION.md** | Ajouter lien dans menu |
| **LEGACY_SYSTEM_DEPRECATION.md** | Supprimer ancien système |

---

## ✅ Checklist Post-Installation

```
[ ] Migration SQL exécutée
[ ] Clé API configurée
[ ] Fichiers copiés
[ ] Page recommander_knn charge
[ ] Admin Features accessible
[ ] Test complet avec 2+ exercices
[ ] Aucune erreur PHP
```

---

## 🚨 3 Erreurs Courantes

| Erreur | Solution |
|--------|----------|
| "API non configurée" | Ajouter vraie clé dans `config/api_keys.php` |
| "Table n'existe pas" | Exécuter `migration_knn.sql` |
| "404 Page introuvable" | Vérifier routes dans `index.php` |

---

## 🎓 Pour En Savoir Plus

- **Comment ça marche ?** → [KNN_IMPLEMENTATION_GUIDE.md](KNN_IMPLEMENTATION_GUIDE.md)
- **Code complet ?** → [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)
- **Ça marche pas ?** → [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Tests avant prod ?** → [VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md)

---

**Durée totale** : ~15 minutes
**Niveau de difficulté** : Facile (juste copy-paste)
**Support** : Tous les fichiers doc fournis

Let's GO ! 🚀
