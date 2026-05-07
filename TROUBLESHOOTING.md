# 🐛 Guide Troubleshooting KNN

## ❌ Erreurs Courantes et Solutions

---

## 1️⃣ "Clé API WorkoutX non configurée"

**Erreur exacte** :
```
Exception: Clé API WorkoutX non configurée. Veuillez renseigner WORKOUTX_API_KEY dans config/api_keys.php
```

**Solutions** :

✅ **Vérifier config/api_keys.php** :
```php
// ❌ MAUVAIS
define('WORKOUTX_API_KEY', 'YOUR_API_KEY_HERE');

// ✅ BON
define('WORKOUTX_API_KEY', 'abc123xyz789');
```

✅ **Récupérer une clé API** :
1. Aller sur https://www.workoutxapp.com
2. S'inscrire (gratuit)
3. Aller dans Settings → API
4. Copier la clé
5. Coller dans `config/api_keys.php`

✅ **Tester si ça marche** :
```php
// test_api.php
require_once 'config/api_keys.php';
require_once 'services/WorkoutXApiService.php';

try {
    $api = new WorkoutXApiService();
    $data = $api->fetchExercisesByName('Pushups');
    echo "✅ API OK : " . count($data) . " exercices trouvés";
} catch (Exception $e) {
    echo "❌ Erreur API : " . $e->getMessage();
}
```

---

## 2️⃣ "L'exercice source n'existe pas ou n'a pas de caractéristiques"

**Erreur** :
```
Exception: L'exercice source n'existe pas ou n'a pas de caractéristiques
```

**Vérifier** :

✅ **L'exercice existe** :
```sql
SELECT * FROM exercice WHERE id_exercice = 3;
-- Doit retourner 1 ligne
```

✅ **L'exercice a des features** :
```sql
SELECT * FROM exercice_feature WHERE id_exercice = 3;
-- Doit retourner 1 ligne
-- Si vide : aller admin_edit_feature pour ajouter
```

✅ **Solution rapide** :
1. Aller Admin → Features KNN
2. Cliquer "Importer API" sur l'exercice
3. Ou remplir manuellement le formulaire
4. Relancer la recommandation

---

## 3️⃣ Tableau "Aucun exercice similaire trouvé"

**Vérifier** :

✅ **Au moins 2 exercices ont des features** :
```sql
SELECT COUNT(*) FROM exercice_feature;
-- Doit être >= 2 (l'un source, au moins 1 autre)
```

✅ **Vérifier les données** :
```sql
SELECT e.id_exercice, e.nom, ef.intensite_calorique 
FROM exercice e 
LEFT JOIN exercice_feature ef ON e.id_exercice = ef.id_exercice
ORDER BY e.nom;
```

Si trop d'exercices sans features :
1. Admin → Features KNN
2. Cliquer sur plusieurs "Importer API"

---

## 4️⃣ "Impossible de contacter l'API WorkoutX"

**Erreur** :
```
Exception: Impossible de contacter l'API WorkoutX
```

**Vérifier** :

✅ **Connexion Internet** :
```bash
ping api.workoutxapp.com
# Doit répondre
```

✅ **URL API correcte** :
```php
// config/api_keys.php
define('WORKOUTX_API_BASE_URL', 'https://api.workoutxapp.com/v1');
// (pas de slash à la fin)
```

✅ **PHP peut faire des requêtes HTTP** :
```php
// test_curl.php
$url = 'https://api.workoutxapp.com/v1/exercises?name=pushups';
$context = stream_context_create([
    'http' => ['method' => 'GET', 'timeout' => 10]
]);
$result = @file_get_contents($url, false, $context);
echo $result ? "✅ OK" : "❌ FAIL";
```

✅ **Vérifier les logs PHP** :
```
Windows : C:\xampp\apache\logs\error.log
Linux : /var/log/apache2/error.log
```

---

## 5️⃣ Erreur 404 sur routes KNN

**Erreur** :
```
404 - Page introuvable
```

**Vérifier** :

✅ **Les routes existent dans index.php** :
```php
case 'recommander_knn':
    $exerciceController->recommanderKnn();
    break;
```

✅ **Pas de typo dans les cas** :
```php
// ❌ MAUVAIS
case 'recommander-knn':

// ✅ BON
case 'recommander_knn':
```

✅ **Les classes/méthodes existent** :
```php
// ExerciceController.php
public function recommanderKnn() { ... }
```

✅ **Les requires sont présents** :
```php
// index.php (haut du fichier)
require_once __DIR__ . '/controllers/ExerciceController.php';
```

---

## 6️⃣ Erreur SQL : "Table 'exercice_feature' n'existe pas"

**Erreur** :
```
SQLSTATE[42S02]: Table 'kool_healthy.exercice_feature' doesn't exist
```

**Solution** :

✅ **Exécuter la migration** :
1. PhpMyAdmin → Select DB `kool_healthy`
2. Onglet SQL
3. Copier-coller `migration_knn.sql` complet
4. Cliquer Execute

✅ **Vérifier résultat** :
```sql
SHOW TABLES;
-- Doit lister : exercice_feature
```

✅ **Données de test** :
```sql
SELECT * FROM exercice_feature;
-- Doit avoir 4 lignes (les exemples)
```

---

## 7️⃣ Erreur validation : "doit être entre 0 et 1"

**Erreur** :
```
Erreur : Intensité Calorique doit être entre 0 et 1.
```

**Solution** :

✅ **Vérifier format** :
```
❌ MAUVAIS : 1.75 (> 1)
❌ MAUVAIS : -0.5 (< 0)
❌ MAUVAIS : abc (pas un nombre)

✅ BON : 0.75
✅ BON : 0.5
✅ BON : 0.25
```

✅ **Décimales** :
```
✅ Un point : 0.75
❌ Une virgule : 0,75 (ne marche pas en DB)
```

---

## 8️⃣ Distance KNN = 0 (même exercice)

**Observation** :
```
Sélectionner "Pompes" → Distance 0.0 pour "Pompes" aussi ?
```

**Explication** :
C'est normal ! Si deux exercices ont exactement les mêmes features, la distance est 0.

**Solution** :
Le KNN exclut l'exercice source, donc ce cas ne devrait pas arriver.

Si ça arrive :
```sql
-- Vérifier pas de doublon id_exercice
SELECT id_exercice, COUNT(*) 
FROM exercice_feature 
GROUP BY id_exercice 
HAVING COUNT(*) > 1;
-- Doit être vide
```

---

## 9️⃣ Réponse API invalide

**Erreur** :
```
Exception: Réponse JSON invalide de l'API
```

**Vérifier** :

✅ **Clé API valide** :
```php
// Tester avec curl
curl -H "X-WorkoutX-Key: YOUR_KEY" \
  "https://api.workoutxapp.com/v1/exercises?name=pushups"
```

✅ **Format réponse** :
- L'API retourne JSON valide
- Contient champ "exercises" ou "results" ?
- Tester avec un nom d'exercice commun

✅ **Déboguer réponse** :
```php
// Dans WorkoutXApiService.php, ligne ~70
echo "<pre>";
echo "Réponse brute : ";
var_dump($response);
echo "</pre>";
```

---

## 🔟 Distance très grande (>1)

**Observation** :
```
Distance : 5.234 (devrait être max ~2)
```

**Explication** :
Chaque dimension va de 0 à 1, donc distance max = √(4×1²) = 2

**Solution** :
Vérifier que toutes les features sont ≤ 1

```sql
SELECT * FROM exercice_feature 
WHERE intensite_calorique > 1 
OR equipement > 1 
OR difficulte > 1 
OR cible_musculaire > 1;
-- Doit être vide
```

---

## 1️⃣1️⃣ Les vues n'affichent rien

**Vérifier** :

✅ **Variables passées aux vues** :
```php
// ExerciceController::recommanderKnn()
$exercices = $this->knnModel->getAllExercises();
$similarExercises = [];
// ... puis include
include __DIR__ . '/../views/front/exercices/recommander_knn.php';
```

✅ **Variables disponibles dans vue** :
```php
// recommander_knn.php
<?php foreach ($exercices as $exercice): ?>
    <!-- $exercice doit être défini -->
<?php endforeach; ?>
```

✅ **Erreur PHP** :
```bash
# Vérifier error.log
tail -50 /path/to/php_error.log
```

---

## 1️⃣2️⃣ Admin Features affiche "Non renseigné" pour tous

**Vérifier** :

✅ **JOIN LEFT correct** :
```sql
SELECT 
    e.id_exercice,
    e.nom,
    ef.id_feature,
    ef.intensite_calorique
FROM exercice e
LEFT JOIN exercice_feature ef ON e.id_exercice = ef.id_exercice
ORDER BY e.nom;
```

✅ **Données existent** :
```sql
SELECT COUNT(*) FROM exercice;          -- >= 1
SELECT COUNT(*) FROM exercice_feature;  -- >= 1
```

✅ **IDs correspondent** :
```sql
-- Pour chaque feature, l'id_exercice doit exister dans exercice
SELECT ef.id_exercice 
FROM exercice_feature ef 
WHERE NOT EXISTS (
    SELECT 1 FROM exercice e WHERE e.id_exercice = ef.id_exercice
);
-- Doit être vide
```

---

## 1️⃣3️⃣ Import API échoue silencieusement

**Vérifier** :

✅ **Exception capturée** :
```php
// AdminController::importFeature()
} catch (Exception $e) {
    // Erreur est affichée en alerte
    $this->errors[] = 'Erreur lors de l\'import API : ' . $e->getMessage();
}
```

✅ **Logs PHP** :
```bash
# Vérifier si erreurs API loggées
grep -i "workoutx" /var/log/apache2/error.log
```

✅ **Tester API directement** :
```php
// Créer test_import.php
require_once 'config/api_keys.php';
require_once 'services/WorkoutXApiService.php';

$api = new WorkoutXApiService();
$data = $api->fetchExercisesByName('Pushups');
$features = $api->normalizeFeatures($data);
echo "<pre>";
print_r($features);
echo "</pre>";
```

---

## 1️⃣4️⃣ Similarité négative ou > 100%

**Problème** :
```
Similarité : -45%  (impossible)
Similarité : 145%  (impossible)
```

**Solution** :
Vérifier formule dans recommander_knn.php :

```php
// ✅ BON
$similarity = max(0, 100 - ($distance * 100));

// ❌ MAUVAIS
$similarity = 100 - ($distance * 100);  // Peut être négatif
```

---

## 1️⃣5️⃣ Charset UTF-8 problèmes

**Symptôme** :
```
Caractères spéciaux affichent mal (é → ??)
```

**Solution** :

✅ **HTML charset** :
```html
<!-- header.php -->
<meta charset="UTF-8">
```

✅ **MySQL charset** :
```sql
-- migration_knn.sql
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
```

✅ **PDO charset** :
```php
// config/Database.php
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', ...);
```

---

## 🔍 Commandes de Débogage

### Vérifier installation complète

```bash
# 1. Fichiers existent ?
ls -la services/WorkoutXApiService.php
ls -la models/KnnModel.php
ls -la views/front/exercices/recommander_knn.php

# 2. Erreurs PHP ?
php -l controllers/ExerciceController.php
php -l models/KnnModel.php

# 3. Base de données ?
mysql -u root kool_healthy -e "SHOW TABLES;"
mysql -u root kool_healthy -e "SELECT * FROM exercice_feature;"
```

### Tester API

```bash
# 1. Avec curl
curl -H "X-WorkoutX-Key: YOUR_KEY" \
  "https://api.workoutxapp.com/v1/exercises?name=pushups"

# 2. Avec PHP
php -r "
require 'config/api_keys.php';
require 'services/WorkoutXApiService.php';
try {
    \$api = new WorkoutXApiService();
    \$data = \$api->fetchExercisesByName('pushups');
    var_dump(\$data);
} catch (Exception \$e) {
    echo \$e->getMessage();
}
"
```

### Tester algorithme

```bash
php -r "
require 'config/Database.php';
require 'models/KnnModel.php';

\$knn = new KnnModel();
\$results = \$knn->getSimilarExercises(3, 3);
var_dump(\$results);
"
```

---

## 📞 Si Tout Else Fails

1. **Vérifier error.log** :
   ```bash
   tail -100 /path/to/php/error.log
   ```

2. **Activer debug mode** :
   ```php
   // index.php (temporaire)
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Ajouter logs** :
   ```php
   error_log("KNN: Exercice source = " . $selectedExerciceId);
   error_log("KNN: Résultats = " . count($similarExercises));
   ```

4. **Tester endpoint par endpoint** :
   - D'abord `admin_features` (plus simple)
   - Puis `recommander_knn` (utilise KNN)
   - Puis `admin_import_feature` (utilise API)

5. **Réinstaller de zéro** :
   ```bash
   # Supprimer table
   mysql -u root kool_healthy -e "DROP TABLE exercice_feature;"
   
   # Réexécuter migration
   # (voir étape 1 du guide)
   ```

---

**Last resort** : Consulter le fichier [KNN_IMPLEMENTATION_GUIDE.md](KNN_IMPLEMENTATION_GUIDE.md) ou [VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md)

Good luck ! 🍀
