# Kool Healthy - MVC Application

Application web pour la gestion de recettes durables avec architecture Model-View-Controller (MVC).

## Structure du Projet

```
all/
├── config.php                 # Configuration globale
├── INDEX.php                  # Routeur principal (entry point)
├── MODEL/                     # Modèles de données
│   ├── Recipe.php            # Modèle Recette
│   ├── Ingredient.php        # Modèle Ingrédient
│   └── User.php              # Modèle Utilisateur
├── CONTROLLER/                # Contrôleurs
│   ├── RecipeC.php           # Contrôleur Recettes
│   ├── IngredientC.php       # Contrôleur Ingrédients
│   └── UserC.php             # Contrôleur Utilisateurs
└── VIEW/                      # Vues
    ├── frontoffice.html      # Interface utilisateur
    ├── backoffice.html       # Interface administration
    ├── css/
    │   ├── frontoffice.css   # Styles front-office
    │   └── backoffice.css    # Styles back-office
    └── js/
        ├── frontoffice.js    # Logique front-office
        └── backoffice.js     # Logique back-office
```

## Architecture MVC

### MODEL (Couche Métier)
Les modèles gèrent les données et la logique métier:
- **Recipe.php**: Gestion des recettes, ingrédients, avis
- **Ingredient.php**: Gestion des ingrédients
- **User.php**: Gestion des utilisateurs

### CONTROLLER (Couche Métier/Presentation)
Les contrôleurs traitent les requêtes et coordonnent modèles et vues:
- **RecipeC.php**: Logique pour les recettes (filtrage, création, suppression)
- **IngredientC.php**: Logique pour les ingrédients
- **UserC.php**: Logique pour les utilisateurs

### VIEW (Couche Présentation)
Les vues affichent les données:
- **frontoffice.html**: Interface utilisateur pour consulter les recettes
- **backoffice.html**: Interface admin pour gérer les données
- CSS et JS associés pour l'interactivité

## Flux de l'Application

1. **Requête HTTP** → `INDEX.php` (routeur)
2. **Routeur** → Détermine l'action/vue à afficher
3. **Contrôleur** → Traite la logique métier
4. **Modèle** → Récupère/modifie les données
5. **Vue** → Affiche le résultat

## Utilisation

### Accès aux Interfaces

**Front Office (Utilisateurs)**
```
http://localhost/all/INDEX.php?view=frontoffice
```
- Consulter les recettes
- Filtrer par critères (difficulté, éco-score, temps)
- Donner des avis

**Back Office (Administrateurs)**
```
http://localhost/all/INDEX.php?view=backoffice
```
- Dashboard avec statistiques
- Gérer les recettes, ingrédients, utilisateurs, avis

### Routes API

Les contrôleurs exposent des endpoints AJAX:

**GET Requests**
```
INDEX.php?action=getAllRecipes
INDEX.php?action=getAllIngredients
INDEX.php?action=getAllUsers
INDEX.php?action=getDashboardStats
```

**POST Requests**
```
action=createRecipe
action=updateRecipe
action=deleteRecipe
action=addReview
action=createIngredient
action=updateIngredient
action=deleteIngredient
action=createUser
action=updateUser
action=toggleBlockUser
```

## Propriétés des Modèles

### Recipe
- `id`: Identifiant unique
- `utilisateurId`: ID du créateur
- `titre`: Nom de la recette
- `instruction`: Préparation
- `temp`: Temps de préparation (min)
- `difficulte`: Facile/Moyen/Difficile
- `ecoScore`: A+/A/B/C
- `ingredients`: Array d'ingrédients avec quantité
- `avis`: Array d'avis (utilisateur, note, commentaire)

### Ingredient
- `id`: Identifiant unique
- `nom`: Nom de l'ingrédient
- `calories`: Calorie par 100g
- `ecoScore`: Score écologique

### User
- `id`: Identifiant unique
- `nom`: Nom complet
- `email`: Adresse email
- `dateInscription`: Date d'inscription
- `statut`: actif/bloque
- `recettesCrees`: Liste des recettes créées
- `avisDonnes`: Liste des avis donnés

## Configuration

Modifier le fichier `config.php` pour:
- Configurer la base de données (si applicable)
- Définir les chemins de l'application
- Configurer les paramètres globaux

## Stockage des Données

Actuellement, les données sont stockées en mémoire (dans les modèles).
Pour une utilisation en production, remplacer par une vraie base de données.

## Points d'Extension

- Ajouter une vraie base de données (remplacer les tableaux statiques)
- Implémenter l'authentification/autorisation
- Ajouter une pagination pour les tables
- Ajouter des validations côté serveur
- Intégrer un système de cache
- Ajouter des logs

## Technologies

- PHP 7.4+
- JavaScript (vanilla)
- CSS3
- Font Awesome Icons
- Google Fonts (Inter)

## Commentaires du Code

Le code est fortement commenté avec sections claires:
- Séparation des responsabilités
- Nommage explicite des variables
- Documentation des fonctions principales
