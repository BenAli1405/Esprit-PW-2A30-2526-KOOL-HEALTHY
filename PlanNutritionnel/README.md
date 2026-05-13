# Module 5 – Plan Nutritionnel · Kool Healthy

## Architecture MVC

```
PlanNutritionnel/
│
├── config.php                          ← Connexion PDO + création auto des tables
│
├── MODEL/
│   ├── PlanNutritionnel.php            ← Classe PlanNutritionnel (entité)
│   └── Repas.php                       ← Classe Repas (entité)
│
├── CONTROLLER/
│   ├── PlanNutritionnelController.php  ← CRUD plans + recommandation + stats
│   └── RepasController.php             ← CRUD repas + suivi calories
│
├── VIEW/
│   ├── mes-plans.php                   ← [FRONT] Liste des plans de l'utilisateur
│   ├── creer-plan.php                  ← [FRONT] Formulaire de création
│   ├── detail-plan.php                 ← [FRONT] Détail plan + planning repas
│   ├── modifier-plan.php               ← [FRONT] Formulaire de modification
│   └── backoffice-plans.php            ← [BACK]  Admin : supervision & stats
│
├── CSS/
│   ├── plans.css                       ← Styles Front office (hérite TFront.html)
│   └── backoffice-plans.css            ← Styles Back office (hérite Tback.html)
│
├── JS/
│   └── plans.js                        ← Interactions communes (modales, filtres…)
│
└── plan_nutritionnel_db.sql            ← Script SQL (tables + données de démo)
```

## Modèle de données

### PlanNutritionnel
| Champ | Type | Description |
|---|---|---|
| `planID` | INT (PK) | Identifiant unique |
| `nom` | string | Nom du plan |
| `calories_journalieres` | float | Objectif calorique quotidien |
| `#utilisateur_id` | int (FK) | Propriétaire du plan |
| `date_debut` | date | Début du plan |
| `date_fin` | date | Fin du plan |
| `statistiques` | float | Pourcentage de progression (0–100) |

### Repas *(1 PlanNutritionnel → N Repas)*
| Champ | Type | Description |
|---|---|---|
| `id` | INT (PK) | Identifiant unique |
| `#planID` | int (FK) | Plan auquel appartient le repas |
| `#recette` | string | Nom/référence de la recette |
| `date` | date | Jour du repas |
| `type_repas` | enum | petit-déjeuner / déjeuner / dîner / collation |
| `statut` | enum | planifié / consommé / annulé |

## Cas d'utilisation implémentés

| Acteur | Use Case | Vue |
|---|---|---|
| Utilisateur | Gérer plan nutritionnel (créer) | `creer-plan.php` |
| Utilisateur | Consulter plan | `detail-plan.php` |
| Utilisateur | Modifier plan | `modifier-plan.php` |
| Utilisateur | Créer / supprimer un repas | `detail-plan.php` |
| Utilisateur | Suivre calories (changer statut repas) | `detail-plan.php` |
| Utilisateur | Recevoir recommandation calorique | `creer-plan.php` + `detail-plan.php` |
| Admin | Gérer tous les plans | `backoffice-plans.php` |
| Admin | Vérifier équilibre nutritionnel | `backoffice-plans.php` (modal) |
| Admin | Statistiques globales | `backoffice-plans.php` (onglet stats) |

## Installation

1. Importer `plan_nutritionnel_db.sql` dans votre base MySQL/MariaDB, **ou** laisser `config.php` créer les tables automatiquement au premier accès.
2. Ajuster `config.php` (host, port, user, password).
3. Placer le dossier dans votre projet à côté du module Recettes.
4. Accéder via :
   - Front office : `VIEW/mes-plans.php`
   - Back office  : `VIEW/backoffice-plans.php`

## Dépendances externes (CDN)

- Google Fonts Inter
- Font Awesome 6 (icônes)
- Chart.js 4.4 (graphiques back office)
