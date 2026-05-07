# 🧹 Nettoyage du Système Ancien (Optionnel)

## 📌 Ancien Système à Déprécier

L'ancien système de règles de recommandation IA (`recommandation_regle`) n'est plus utilisé par le nouveau système KNN.

Vous avez le choix :
1. **Laisser intouché** (recommandé pour la transition) ✅
2. **Supprimer complètement** (si certain de ne plus l'utiliser) ⚠️

---

## Option 1️⃣ : CONSERVATION (Recommandée)

**Aucune action requise.**

Les fichiers existants restent fonctionnels :
- Route `recommander_ia` fonctionne toujours
- Table `recommandation_regle` intacte
- Modèle `RecommandationModel.php` intact
- Vue `views/front/entrainements/recommander.php` intact

Cela permet une **transition progressive** vers KNN.

---

## Option 2️⃣ : SUPPRESSION COMPLÈTE

Si vous êtes **absolument certain** de ne jamais utiliser l'ancien système, voici ce à supprimer :

### ❌ Fichiers à Supprimer

```
models/RecommandationModel.php          // Modèle ancien
views/front/entrainements/recommander.php  // Vue ancienne
```

### 📝 Fichiers à Modifier

#### **controllers/EntrainementController.php**

Trouver la méthode `recommend()` (vers ligne 80-100) :

```php
public function recommend()
{
    $suggestions = [];
    $selectedType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $selectedType = trim($_POST['type_repas'] ?? '');
        if ($selectedType === '') {
            $this->errors[] = 'Veuillez choisir un type de repas.';
        } else {
            $suggestions = $this->recommendationModel->findByTypeRepas($selectedType);
            if (empty($suggestions)) {
                $this->errors[] = 'Aucune recommandation disponible pour ce type de repas.';
            }
        }
    }

    $layout = 'front';
    $action = 'recommander_ia';
    $pageTitle = 'Kool Healthy | Recommander IA';
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/front/entrainements/recommander.php';
```

**À faire** : Supprimer toute cette méthode

#### **index.php**

Trouver la route (vers ligne 45) :

```php
case 'recommander_ia':
    $entrainementController->recommend();
    break;
```

**À faire** : Supprimer ce case (ou laisser, car ne fait rien si méthode n'existe pas)

### 🗑️ Base de Données

Dans PhpMyAdmin, exécuter :

```sql
-- Optionnel : Supprimer la table
DROP TABLE IF EXISTS recommandation_regle;
```

---

## 📋 Checklist de Dépréciation

```
Si vous choisissez la SUPPRESSION COMPLÈTE :

[ ] 1. Supprimer recommandation_regle table (PhpMyAdmin)
[ ] 2. Supprimer models/RecommandationModel.php
[ ] 3. Supprimer views/front/entrainements/recommander.php
[ ] 4. Supprimer require_once RecommandationModel (AdminController)
[ ] 5. Supprimer require_once RecommandationModel (EntrainementController)
[ ] 6. Supprimer private $recommendationModel (AdminController)
[ ] 7. Supprimer new RecommandationModel() (constructeur AdminController)
[ ] 8. Supprimer new RecommandationModel() (constructeur EntrainementController)
[ ] 9. Supprimer méthodes recommend/listRegles/etc (EntrainementController/AdminController)
[ ] 10. Supprimer cases 'recommander_ia', 'admin_regles', etc (index.php)
[ ] 11. Supprimer liens dans header.php
```

---

## ⚠️ Attention Importante

**NE SUPPRIMEZ PAS si** :
- ✅ Vous avez des utilisateurs actifs utilisant `recommander_ia`
- ✅ Des données critiques dans `recommandation_regle`
- ✅ Autres parties du code dépendent du modèle

**SUPPRIMEZ SI** :
- ✅ Système en développement/test
- ✅ Transition complète vers KNN
- ✅ Aucun utilisateur actif

---

## 🎯 Recommandation

**Laissez l'ancien système en place pour l'instant.**

Les deux systèmes peuvent coexister :
- **Ancien** : `recommander_ia` (règles fixes)
- **Nouveau** : `recommander_knn` (algorithme dynamique)

Vous pouvez faire une **transition progressive** et supprimer plus tard quand KNN est totalement stable.

---

**Date** : 3 mai 2026  
**Impact** : Aucun risque avec option 1 (conservation)  
**Durée nettoyage** : ~15 min si option 2
