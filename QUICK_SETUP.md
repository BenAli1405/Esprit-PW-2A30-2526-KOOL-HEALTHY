# 📋 CHECKLIST D'IMPLÉMENTATION RAPIDE

## Copier/Coller - 30 Secondes par Étape

### ✅ Étape 1 : SQL (PhpMyAdmin)

```sql
-- Copier tout de migration_knn.sql
-- PhpMyAdmin → SQL → Paste → Execute
```

**Vérifier** :
```sql
SELECT COUNT(*) FROM exercice_feature;
-- Résultat : 4 ✓
```

---

### ✅ Étape 2 : API Key

**Fichier** : `config/api_keys.php`

```php
define('WORKOUTX_API_KEY', 'VOTRE_CLE_ICI');
```

👉 Clé : https://www.workoutxapp.com (5 min signup)

---

### ✅ Étape 3 : Copier les Fichiers

```
✓ services/WorkoutXApiService.php
✓ models/KnnModel.php
✓ views/front/exercices/recommander_knn.php
✓ views/back/exercices/features_list.php
✓ views/back/exercices/features_form.php
✓ config/api_keys.php (déjà fait step 2)
✓ migration_knn.sql
```

---

### ✅ Étape 4 : Routes (index.php)

Vérifier que ces 4 cases existent :

```php
case 'recommander_knn':
    $exerciceController->recommanderKnn();
    break;

case 'admin_features':
    $adminController->listFeatures();
    break;

case 'admin_edit_feature':
    $adminController->editFeature();
    break;

case 'admin_import_feature':
    $adminController->importFeature();
    break;
```

Si manquant → Ajouter manuellement

---

### ✅ Étape 5 : Test Rapide

**URL 1** : http://localhost/kool_healthy3/?action=recommander_knn
→ Doit afficher formulaire

**URL 2** : http://localhost/kool_healthy3/?action=admin_features
→ Doit afficher liste exercices

Si erreurs → Lire [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

### ✅ Étape 6 : Ajouter Lien Menu (Optionnel)

**Fichier** : `views/layout/header.php`

Chercher ligne ~30 :
```html
<a href="index.php?action=recommander_ia">Recommander IA</a>
```

Ajouter après :
```html
<a href="index.php?action=recommander_knn">KNN Exercices</a>
```

---

## 🎯 Validation Post-Install

```
✓ Migration SQL exécutée
✓ API key configurée
✓ 6 fichiers copiés
✓ Routes ajoutées dans index.php
✓ Page recommander_knn charge
✓ Page admin_features charge
✓ Aucune erreur PHP
✓ Lien menu ajouté (optionnel)
```

Si tout ✓ → Installation réussie ! 🚀

---

## 🆘 Si Erreur

| Erreur | Solution |
|--------|----------|
| 404 Page introuvable | Vérifier routes index.php |
| Table n'existe pas | Exécuter migration_knn.sql |
| API non configurée | Ajouter vraie clé dans config/api_keys.php |
| Affichage vide | Lire TROUBLESHOOTING.md |

---

## 📚 Documentation Complète

Si vous avez besoin de plus d'infos :

| Durée | Fichier | Contenu |
|-------|---------|---------|
| 5 min | [README_KNN.md](README_KNN.md) | Vue d'ensemble |
| 30 min | [KNN_IMPLEMENTATION_GUIDE.md](KNN_IMPLEMENTATION_GUIDE.md) | Tous les détails |
| 45 min | [VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md) | 50+ tests |
| 30 min | [TROUBLESHOOTING.md](TROUBLESHOOTING.md) | Problèmes + solutions |

---

**Total installation** : 5-10 minutes  
**Niveau** : Facile (copy-paste)  
**Support** : 8 fichiers documentation  

✅ **C'est parti !**
